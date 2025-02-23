<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, Store};
use App\Models\Utility;
use App\Models\Cart;
use App\Models\{Customer, Product, User};
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\ShippingZone;
use App\Models\TaxOption;
use App\Models\DeliveryAddress;
use App\Models\TaxMethod;
use App\Models\Shipping;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Api\ApiController;
use Session;
use Illuminate\Support\Facades\Crypt;
use App\DataTables\AbandonCartDataTable;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function cart_list_sidebar(Request $request, $storeSlug)
    {
        $store = Cache::remember('store_' . $storeSlug, 3600, function () use ($storeSlug) {
            return Store::where('slug', $storeSlug)->first();
        });
        if (empty($store)) {
            $return['status'] = false;
            $return['message'] = __('Something went wrong');
            $return['sub_total'] = 0;
            return response()->json($return);
        }
        $currentTheme = $store->theme_id;
        $slug = $store->slug;
        if (!$request->shipping_price) {
            $shippingMethod = ShippingMethod::find($request->method_id);

            if ($shippingMethod) {
                $request['shipping_final_price'] = $shippingMethod->cost;
            } else {
                $request['shipping_final_price'] = 0;
            }
        } else {
            $request['shipping_final_price'] = $request->shipping_price ?? 0;
        }

        if (auth('customers')->guest()) {
            $response = Cart::cart_list_cookie($request->all(), $store->id);
            $response = json_decode(json_encode($response));
        } else {
            $request->merge(['customer_id' => auth('customers')->user()->id, 'store_id' => $store->id, 'slug' => $slug,'theme_id' => $currentTheme,'status' => true, 'order_type' => $request->order_type ?? '']);

            $api = new ApiController();
            $data = $api->cart_list($request, $storeSlug);
            $response = $data->getData();
        }
        $return['status'] = $response->status;
        $return['message'] = $response->message;
        $return['sub_total'] = 0;
        $tax_option = TaxOption::where('store_id', $store->id)
            ->where('theme_id', $store->theme_id)
            ->pluck('value', 'name')->toArray();
        if ($response->status == 1) {
            $default_checkout_btn = 'off';
            $currency = Utility::GetValueByName('CURRENCY', $currentTheme, $store->id);
            $currency_name = Utility::GetValueByName('CURRENCY_NAME', $currentTheme, $store->id);
            $return['cart_total_product'] = $response->data->cart_total_product;
            $return['html'] = view('front_end.sections.pages.cart-list-sidebar', compact('slug', 'response', 'currency', 'currency_name', 'tax_option', 'currentTheme', 'store'))->render();
            $page_json = arrayToObject(getInnerPageJson($currentTheme, $store->id, 'cart_page'));
            $return['checkout_html'] = view('front_end.sections.pages.cart-list', compact('slug', 'response', 'currency', 'currency_name', 'tax_option', 'currentTheme', 'store', 'page_json'))->render();
            $return['checkout_html_2'] = view('front_end.sections.pages.checkout-cart-list', compact('slug', 'response', 'currency', 'currency_name', 'tax_option', 'currentTheme', 'store', 'default_checkout_btn'))->render();
            $return['checkout_html_products'] = view('front_end.sections.pages.checkout-product-list', compact('response', 'currency', 'currency_name', 'currentTheme', 'store'))->render();
            $return['checkout_amounts'] = view('front_end.sections.pages.checkout-amount', compact('response', 'currency', 'currency_name', 'currentTheme', 'store'))->render();
            if (module_is_active('SkipCart')) {
                $enable_skip_cart =  \App\Models\Utility::GetValueByName('enable_skip_cart', $currentTheme, $store->id);
                $checkout_setting = Utility::GetValueByName('skip_cart_checkout_setting', $store->theme_id, $store->id);
                if ((isset($checkout_setting) && $checkout_setting == 'sticky_cart') && $enable_skip_cart == 'on') {
                    $return['sticky_product_list'] = view('skip-cart::theme.sticky-product-list', compact('response', 'currency', 'currency_name', 'currentTheme', 'store', 'slug'))->render();
                }
            }
            $return['sub_total'] =  $response->data->final_price ?? ($response->data->sub_total ?? 0);
        }
        return response()->json($return);
    }

    public function cart_remove(Request $request, $slug)
    {
        $slug = !empty($request->slug) ? $request->slug : '';
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
            return Store::where('slug', $slug)->first();
        });
        // $theme_id = $store->theme_id;

        if (auth('customers')->guest()) {
            $Carts = Cookie::get('cart');
            $cart = Cart::where('cookie_session_id', $Carts)->where('product_id',$request->product_id)->where('variant_id',$request->variant_id)->delete();
            Cookie::queue('cart', $Carts, 1440);
        } else {
            $cart = Cart::where('id', $request->cart_id)->first();

            // activity log
            if ($cart) {
                $ActivityLog = new ActivityLog();
                $ActivityLog->customer_id = $cart->customer_id ?? null;
                $ActivityLog->log_type = 'remove to cart';
                $ActivityLog->remark = json_encode(
                    [
                        'product' => $cart->product_id,
                        'variant' => $cart->variant_id,
                    ]
                );
                $ActivityLog->theme_id = $cart->theme_id;
                $ActivityLog->store_id = $store->id;
                $ActivityLog->save();
                $cart->delete();
            }
        }

        $return['status'] = 1;
        return response()->json($return);
    }

    public function change_cart(Request $request, $storeSlug)
    {
        $slug = !empty($storeSlug) ? $storeSlug : '';
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
            return Store::where('slug', $slug)->first();
        });
        $theme_id = $store->theme_id;

        $cart_id = $request->cart_id;
        $quantity_type = $request->quantity_type;
        if (auth('customers')->guest()) {
            $Carts = Cookie::get('cart');
            

            $param = [
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
                'quantity_type' => $quantity_type,
            ];

            $request->merge($param);

            $response = Cart::cart_qty_cookie($request);
            return response()->json($response);
        } else {
            $Cart = Cart::find($cart_id);

            $param = [
                'customer_id' => $Cart->customer_id,
                'product_id' => $Cart->product_id,
                'variant_id' => $Cart->variant_id,
                'quantity_type' => $quantity_type,
                'theme_id' => $theme_id,
                'slug' => $slug,

            ];

            $request->merge($param);

            $api = new ApiController();
            $data = $api->cart_qty($request, $slug);
            $response = $data->getData();

            return response()->json($response);
        }
    }

    public function product_cartlist(Request $request, $slug)
    {
        $slug = !empty($slug) ? $slug : '';
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
            return Store::where('slug', $slug)->first();
        });
        $theme_id = $store->theme_id;

        $product_id = $request->product_id;
        $variant_id = $request->variant_id;
        $qty = $request->qty;
        $size_data = '';
        if (module_is_active('SizeGuideline')) {
            $size_data = $request->size_data ?? '';
        }
        if (!auth('customers')->user()) {
            $response =  Cart::addtocart_cookie($product_id, $variant_id, $qty, $theme_id, $store->id, $size_data);
            return response()->json($response);
        }
        $customer_id = auth('customers')->user()->id;

        $request->request->add(['variant_id' => $variant_id,'product_id' => $product_id, 'customer_id' => $customer_id, 'qty' => $qty, 'store_id' => $store->id, 'slug' => $slug, 'theme_id' => $theme_id, 'order_type' => $request->order_type ?? '']);
        $api = new ApiController();
        $data = $api->addtocart($request, $slug);
        $response = $data->getData();

        return response()->json($response);
    }

    public function get_shipping_method(Request $request, $slug)
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
            return Store::where('slug', $slug)->first();
        });
        $shippingMethods = ShippingMethod::find($request->method_id);
        $theme_id = $store->theme_id;
        if (auth('customers')->guest()) {
            $response = Cart::cart_list_cookie($request->all(), $store->id);
            $response = json_decode(json_encode($response));
        } else {
            $address = DeliveryAddress::find($request->billing_address_id);
            if ($address) {
                $parms['billing_info']['delivery_country'] = $address->country_id;
                $parms['billing_info']['delivery_state'] = $address->state_id;
                $parms['billing_info']['delivery_city'] = $address->city_id;
                $request->merge($parms);
            }
            $request->merge(['customer_id' => auth('customers')->user()->id, 'store_id' => $store->id, 'slug' => $slug, 'theme_id' => $theme_id]);
            $api = new ApiController();
            $data = $api->cart_list($request, $slug);
            $response = $data->getData();
        }
        if (!empty($shippingMethods)) {
            $shipp_name = $shippingMethods->method_name;

            if ($shipp_name == 'Flat Rate') {
                if ($shippingMethods->calculation_type == 1) {
                    $cost_totle = Cart::calculateFlatRateShippingAmount($shippingMethods, $shippingMethods->cost, $response->data->product_list);
                    $shipping_final_price =  $cost_totle;
                    $total_shipping_price = $cost_totle;
                } else {
                    $cost_totle = Cart::calculateFlatRateShippingAmount($shippingMethods, $shippingMethods->cost, $response->data->product_list);
                    $shipping_final_price =  $cost_totle;
                    $total_shipping_price = $cost_totle;
                }
            } elseif ($shipp_name == 'Local pickup') {
                $cost_totle = $shipping_final_price =  $shippingMethods->cost;
                $total_shipping_price = $shipping_final_price;
            } else {
                $cost_totle = $shipping_final_price =  0;

                $total_shipping_price = $shipping_final_price;
            }
            $product_digital_id = $product_digital_not_id = [];
            foreach ($response->data->product_list as $item) {
                $products_datas = Product::find($item->product_id);
                if ($products_datas->product_type == 'digital') {
                    $product_digital_id[] = $products_datas->id;
                } else {
                    $product_digital_not_id[] = $products_datas->id;
                }
            }

            if (count($product_digital_id) > 0 && count($product_digital_not_id) == 0) {
                $total_shipping_price = 0;
                $shipping_final_price = 0;
            }
            $price = $cost_totle;
            $total_price = $total_shipping_price;

            if (module_is_active('FreeShippingPopup')) {
                $admin          = User::find($store->created_by);
                $plan           = Plan::find($admin->plan_id);
                $adminSetting   = getAdminAllSetting($store->created_by, $store->id, $store->theme_id);
                if (isset($plan->modules) && strpos($plan->modules, 'FreeShippingPopup') !== false) {
                    if (isset($adminSetting['free_shipping_popup_enabled']) && $adminSetting['free_shipping_popup_enabled'] == 'on' && isset($adminSetting['free_shipping_apply_price']) && ($response->data->total_sub_price ?? 0) >= $adminSetting['free_shipping_apply_price']) {
                        $return['shipping_final_price'] = 0;
                        $return['shipping_total_price'] = currency_format_with_sym(($response->data->total_sub_price ?? 0), $store->id, $store->theme_id);
                        $return['total_sub_price'] = ($response->data->total_sub_price ?? 0);
                    } else {
                        $return['shipping_final_price'] = $shipping_final_price ?? 0;
                        $return['shipping_total_price'] = currency_format_with_sym(($response->data->total_sub_price ?? 0) + $total_shipping_price, $store->id, $store->theme_id);
                        $return['total_sub_price'] = ($response->data->total_sub_price ?? 0) + $shipping_final_price;
                    }
                } else {
                    $return['shipping_final_price'] = $shipping_final_price ?? 0;
                    $return['shipping_total_price'] = currency_format_with_sym(($response->data->total_sub_price ?? 0) + $total_shipping_price, $store->id, $store->theme_id);
                    $return['total_sub_price'] = ($response->data->total_sub_price ?? 0) + $shipping_final_price;
                }
            } else {
                $return['shipping_final_price'] = $shipping_final_price ?? 0;
                $return['shipping_total_price'] = currency_format_with_sym(($response->data->total_sub_price ?? 0) + $total_shipping_price, $store->id, $store->theme_id);
                $return['total_sub_price'] = ($response->data->total_sub_price ?? 0) + $shipping_final_price;
            }

            $return['cart_total_product'] = $response->data->cart_total_product ?? null;
            $return['cart_total_qty'] = $response->data->cart_total_qty ?? null;
            $return['original_price'] = $response->data->original_price ?? 0;
            // $return['total_final_price'] = $response->data->total_final_price ?? 0;
            $return['total_final_price'] = $total_shipping_price ?? 0;
            $return['final_price'] = $response->data->final_price ?? 0;
            $return['total_coupon_price'] = $response->data->total_coupon_price ?? 0;
            $return['tax_id_value'] = $response->data->tax_id ?? null;
            $return['final_tax_price'] = $response->data->total_tax_price ?? 0;
            $return['tax_price'] =($response->data->total_tax_price ?? 0);
            // $return['shipping_final_price'] = currency_format_with_sym(($response->data->total_final_price ?? 0), $store->id, $store->theme_id);
            $return['shipping_final_price'] = currency_format_with_sym(($total_shipping_price ?? 0), $store->id, $store->theme_id);
            $return['final_tax_price_with_currency'] = currency_format_with_sym(($response->data->total_tax_price ?? 0), $store->id, $store->theme_id);
            $return['sub_total'] = currency_format_with_sym(($response->data->total_final_price ?? 0), $store->id, $store->theme_id);
            $return['message'] = 'Add Shipping success';
            return response()->json($return);
        } else {
            $product_digital_id = $product_digital_not_id = [];
            foreach ($response->data->product_list as $item) {
                $products_datas = Product::find($item->product_id);
                if ($products_datas->product_type == 'digital') {
                    $product_digital_id[] = $products_datas->id;
                } else {
                    $product_digital_not_id[] = $products_datas->id;
                }
            }

            if (count($product_digital_id) > 0 && count($product_digital_not_id) == 0) {
                $total_shipping_price = 0;
                $shipping_final_price = 0;
            }
            $return['shipping_final_price'] = $shipping_final_price ?? 0;
            $return['cart_total_product'] = $response->data->cart_total_product ?? null;
            $return['cart_total_qty'] = $response->data->cart_total_qty ?? null;
            $return['original_price'] = $response->data->original_price ?? 0;
            // $return['total_final_price'] = $response->data->total_final_price ?? 0;
            $return['total_final_price'] = $total_shipping_price ?? 0;
            $return['final_price'] = $response->data->final_price ?? 0;
            $return['total_coupon_price'] = $response->data->total_coupon_price ?? 0;
            $return['total_sub_price'] = $response->data->total_sub_price ?? 0;
            $return['tax_id_value'] = $response->data->total_tax_id ?? null;
            $return['shipping_total_price'] = currency_format_with_sym(($response->data->total_sub_price ?? 0), $store->id, $store->theme_id);
            $return['final_tax_price'] = $response->data->total_tax_price ?? 0;
            $return['tax_price'] =($response->data->total_tax_price ?? 0);
            // $return['shipping_final_price'] = currency_format_with_sym(($response->data->total_final_price ?? 0), $store->id, $store->theme_id);
            $return['shipping_final_price'] = currency_format_with_sym(($total_shipping_price ?? 0), $store->id, $store->theme_id);
            $return['final_tax_price_with_currency'] = currency_format_with_sym(($response->data->total_tax_price ?? 0), $store->id, $store->theme_id);
            $return['sub_total'] = currency_format_with_sym(($response->data->total_final_price ?? 0), $store->id, $store->theme_id);
            $return['message'] = __('Add Shipping success');
            return response()->json($return);
        }
    }

    public function get_shipping_data(Request $request, $slug)
    {
        $totalCouponAmount = $request['total_coupon_amount'];
        $Products = $request['product_id'];
        $Product = Product::find($Products);

        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
            return Store::where('slug', $slug)->first();
        });
        $theme_id = $store->theme_id;
        $CURRENCY = \App\Models\Utility::GetValueByName('defult_currancy_symbol', $theme_id, $store->id);
        $code = trim($request->coupon_code);
        $coupon = Coupon::whereRaw('BINARY `coupon_code` = ?', [$code])->where('theme_id', $theme_id)->where('store_id', $store->id)->first();
        $user = User::where('id', $store->created_by)->first();
        if ($user) {
            $plan = Cache::remember('plan_details_' . $user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
        }
        if ($plan->shipping_method == 'on') {
            if (auth('customers')->user()) {
                $request->merge(['customer_id' => auth('customers')->user()->id, 'store_id' => $store->id, 'slug' => $slug, 'theme_id' => $theme_id]);
                $api = new ApiController();
                $data = $api->cart_list($request, $slug);
                $response = $data->getData();
                $sub_total = $response->data->sub_total;

                $Delivery_Address = DeliveryAddress::where('customer_id', auth('customers')->user()->id)->where('theme_id', $theme_id)->where('store_id', $store->id)->first();

                if ($Delivery_Address == "") {
                    $country = $request->countryId;
                    $state_id = $request->stateId;

                    $Shipping_zone = ShippingZone::where('theme_id', $theme_id)->where('store_id', $store->id)->where('country_id', $country)->where('state_id', $state_id)->first();
                } else {
                    $User_address = $request['address_id'] ?? $request['billing_addres_id'];
                    $Delivery_Address = DeliveryAddress::where('id', $User_address)->where('customer_id', auth('customers')->user()->id)->where('theme_id', $theme_id)->where('store_id', $store->id)->first();
                    $country = !empty($Delivery_Address->country_id) ? $Delivery_Address->country_id : '';
                    $state_id = !empty($Delivery_Address->state_id) ? $Delivery_Address->state_id : '';

                    $Address = DeliveryAddress::find($User_address);
                    $Shipping_zone = ShippingZone::where('theme_id', $theme_id)->where('store_id', $store->id)->where('country_id', $country)->where('state_id', $state_id)->first();
                }

                $shipping_requires = ShippingMethod::freeShipping();
                if (!empty($Shipping_zone)) {
                    $methods = ShippingMethod::where('zone_id', $Shipping_zone->id)->where('theme_id', $theme_id)->where('store_id', $store->id)->get();
                    $shippingMethods = [];
                    $freeShippingMethod = null;
                    foreach ($methods as $method) {
                        if ($method->shipping_requires == '1' || $method->shipping_requires == '3' || $method->shipping_requires == '4') {
                            if ($method->method_name == "Free shipping") {
                                if ($method->cost < $sub_total) {
                                    $freeShippingMethod = $method;
                                }
                            } else {
                                $shippingMethods[] = $method;
                            }
                        } elseif ($method->shipping_requires == '2' || $method->shipping_requires == '5') {
                            if ($method->method_name == "Free shipping") {
                                if (!empty($coupon)) {
                                    if ($method->cost < $sub_total) {
                                        $freeShippingMethod = $method;
                                    }
                                }
                            } else {
                                $shippingMethods[] = $method;
                            }
                        } else {
                            $shippingMethods[] = $method;
                        }
                    }
                    if ($freeShippingMethod !== null) {
                        $shippingMethods = [$freeShippingMethod];
                    }
                } else {
                    $Shipping_zone = ShippingZone::where('theme_id', $theme_id)
                        ->where('store_id', $store->id)
                        ->where(function ($query) {
                            $query->where('country_id', '')
                                ->orWhereNull('country_id');
                        })
                        ->where(function ($query) {
                            $query->where('state_id', '')
                                ->orWhereNull('state_id');
                        })
                        ->first();
                    if ($Shipping_zone) {
                        $methods = ShippingMethod::where('zone_id', $Shipping_zone->id)->where('theme_id', $theme_id)->where('store_id', $store->id)->get();
                        $shippingMethods = [];
                        $freeShippingMethod = null;
                        foreach ($methods as $method) {
                            if ($method->shipping_requires == '1' || $method->shipping_requires == '3' || $method->shipping_requires == '4') {
                                if ($method->method_name == "Free shipping") {
                                    if ($method->cost < $sub_total) {
                                        $freeShippingMethod = $method;
                                    }
                                } else {
                                    $shippingMethods[] = $method;
                                }
                            } elseif ($method->shipping_requires == '2' || $method->shipping_requires == '5') {
                                if ($method->method_name == "Free shipping") {
                                    if (!empty($coupon)) {
                                        if ($method->cost < $sub_total) {
                                            $freeShippingMethod = $method;
                                        }
                                    }
                                } else {
                                    $shippingMethods[] = $method;
                                }
                            } else {
                                $shippingMethods[] = $method;
                            }
                        }
                        if ($freeShippingMethod !== null) {
                            $shippingMethods = [$freeShippingMethod];
                        }
                    }
                }
            } else {
                $country = $request->countryId;
                $state_id = $request->stateId;

                $response = Cart::cart_list_cookie($request->all(), $store->id);
                $response = json_decode(json_encode($response));
                $Shipping_zone = ShippingZone::where('theme_id', $theme_id)->where('store_id', $store->id)->where('country_id', $country)->where('state_id', $state_id)->first();

                $shipping_requires = ShippingMethod::freeShipping();

                $sub_total = $response->data->sub_total;

                if (!empty($Shipping_zone)) {
                    $methods = ShippingMethod::where('zone_id', $Shipping_zone->id)->where('theme_id', $theme_id)->where('store_id', $store->id)->get();
                    $shippingMethods = [];
                    $freeShippingMethod = null;
                    foreach ($methods as $method) {
                        if ($method->shipping_requires == '1' || $method->shipping_requires == '3' || $method->shipping_requires == '4') {
                            if ($method->method_name == "Free shipping") {
                                if ($method->cost < $sub_total) {
                                    $freeShippingMethod = $method;
                                }
                            } else {
                                $shippingMethods[] = $method;
                            }
                        } elseif ($method->shipping_requires == '2' || $method->shipping_requires == '5') {
                            if ($method->method_name == "Free shipping") {
                                if (!empty($coupon)) {
                                    if ($method->cost < $sub_total) {
                                        $freeShippingMethod = $method;
                                    }
                                }
                            } else {
                                $shippingMethods[] = $method;
                            }
                        } else {
                            $shippingMethods[] = $method;
                        }
                    }

                    if ($freeShippingMethod !== null) {
                        $shippingMethods = [$freeShippingMethod];
                    }
                } else {
                    $Shipping_zone = ShippingZone::where('theme_id', $theme_id)
                        ->where('store_id', $store->id)
                        ->where(function ($query) {
                            $query->where('country_id', '')
                                ->orWhereNull('country_id');
                        })
                        ->where(function ($query) {
                            $query->where('state_id', '')
                                ->orWhereNull('state_id');
                        })
                        ->first();
                    if ($Shipping_zone) {
                        $methods = ShippingMethod::where('zone_id', $Shipping_zone->id)->where('theme_id', $theme_id)->where('store_id', $store->id)->get();

                        $shippingMethods = [];
                        $freeShippingMethod = null;
                        foreach ($methods as $method) {
                            if ($method->shipping_requires == '1' || $method->shipping_requires == '3' || $method->shipping_requires == '4') {
                                if ($method->method_name == "Free shipping") {
                                    if ($method->cost < $sub_total) {
                                        $freeShippingMethod = $method;
                                    }
                                } else {
                                    $shippingMethods[] = $method;
                                }
                            } elseif ($method->shipping_requires == '2' || $method->shipping_requires == '5') {
                                if ($method->method_name == "Free shipping") {
                                    if (!empty($coupon)) {
                                        if ($method->cost < $sub_total) {
                                            $freeShippingMethod = $method;
                                        }
                                    }
                                } else {
                                    $shippingMethods[] = $method;
                                }
                            } else {
                                $shippingMethods[] = $method;
                            }
                        }
                        if ($freeShippingMethod !== null) {
                            $shippingMethods = [$freeShippingMethod];
                        }
                    }
                }
            }

            $return['CURRENCY'] = $CURRENCY;
            $return['shipping_method'] = $shippingMethods ?? null;

            return response()->json($return);
        }

        $return['CURRENCY'] = $CURRENCY;
        $return['shipping_method'] = "";

        return response()->json($return);
    }


    public function get_tax_data(Request $request, $slug)
    {

        $Products = $request['product_id'];
        $Product = Product::find($Products);
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
            return Store::where('slug', $slug)->first();
        });
        $user = User::where('id', $store->created_by)->first();
        if ($user) {
            $plan = Cache::remember('plan_details_' . $user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
        }

        $theme_id = $store->theme_id;

        $code = trim($request->coupon_code);
        $coupon = Coupon::whereRaw('BINARY `coupon_code` = ?', [$code])->where('theme_id', $theme_id)->where('store_id', $store->id)->first();
        $CURRENCY = \App\Models\Utility::GetValueByName('defult_currancy_symbol', $theme_id, $store->id);
        $tax_option = TaxOption::where('store_id', $store->id)
            ->where('theme_id', $store->theme_id)
            ->pluck('value', 'name')->toArray();
        if ($Product == null) {
            $taxs = TaxMethod::where('tax_id', $request['tax_id_value'])->where('theme_id', $theme_id)->where('store_id', $store->id)->orderBy('priority', 'asc')->get();
            $tax_id = $request['tax_id_value'];
        } else {
            $taxs = TaxMethod::where('tax_id', $Product->tax_id)->where('theme_id', $theme_id)->where('store_id', $store->id)->orderBy('priority', 'asc')->get();
            $tax_id = $Product->tax_id;
        }
        $return = '';
        if (auth('customers')->user()) {
            $request->merge(['customer_id' => auth('customers')->user()->id, 'store_id' => $store->id, 'slug' => $slug, 'theme_id' => $theme_id]);
            $address_id = $request->billing_address_id ?? $request->address_id;
            $other_info = $request['billing_info'];

            if ($address_id) {
                $billing_address  = DeliveryAddress::find($address_id);
                $country = !empty($billing_address->country_id) ? $billing_address->country_id : $other_info['delivery_country'];
                $state_id = !empty($billing_address->state_id) ? $billing_address->state_id : $other_info['delivery_state'];
                $city_id = !empty($billing_address->city_id) ? $billing_address->city_id : $other_info['delivery_city'];
            }


            $api = new ApiController();
            $data = $api->cart_list($request, $slug);
            $response = $data->getData();

            $sub_total = $response->data->sub_total;

            $country = !empty($request['countryId']) ? $request['countryId'] : ($other_info['delivery_country'] ?? 0);
            $state_id = !empty($request['stateId']) ? $request['stateId'] : ($other_info['delivery_state'] ?? 0);

            $city_id = !empty($request['cityId']) ? $request['cityId'] : ($other_info['delivery_city'] ?? 0);

            $tax_price = 0;

            if (count($taxs) > 0 && $plan->shipping_method != 'on') {
                $tax_price = 0;

                foreach ($taxs as $tax) {
                    $countryMatch = (!$tax->country_id || $country == $tax->country_id);
                    $stateMatch = (!$tax->state_id || $state_id == $tax->state_id);
                    $cityMatch = (!$tax->city_id || $city_id == $tax->city_id);

                    if ($countryMatch && $stateMatch && $cityMatch) {
                        $amount = $tax->tax_rate * $Product->sale_price / 100;
                        $tax_price += $amount;
                    }
                }
                if (isset($tax_option['round_tax']) && $tax_option['round_tax'] == 1) {
                    $tax_price = round($tax_price);
                } else {
                    $tax_price = $tax_price;
                }
                $return = [
                    'sale_price' => SetNumber($Product->sale_price),
                    'tax_price' => SetNumber($tax_price),
                    'tax_id_value' => $tax_id,
                    'final_total_amount' => SetNumber($Product->sale_price + $tax_price),
                    'CURRENCY' => $CURRENCY,
                ];
            } elseif (count($taxs) > 0 && $plan->shipping_method == 'on') {

                $shipping = $request['shipping_final_price'] ? $request['shipping_final_price'] : (!empty($request->data->shipping_original_price) ? $response->data->shipping_original_price : 0);
                $coupon_amount = 0;
                if ($request['total_coupon_amount'] == '') {
                    $total_amount = $sub_total + $shipping;
                } else {
                    $total_amount = ($sub_total - $request['total_coupon_amount']) + $shipping;
                }
                foreach ($taxs as $tax) {

                    $countryMatch = (!$tax->country_id || $country == $tax->country_id);
                    $stateMatch = (!$tax->state_id || $state_id == $tax->state_id);
                    $cityMatch = (!$tax->city_id || $city_id == $tax->city_id);

                    if ($countryMatch && $stateMatch && $cityMatch) {

                        $amount = $tax->tax_rate * $total_amount / 100;
                        $tax_price += $amount;
                    }
                }
                if ($tax_option['round_tax'] ?? '' == 1) {
                    $tax_price = round($tax_price);
                } else {
                    $tax_price = $tax_price;
                }

                $return = [
                    'sale_price' => SetNumber($response->data->sub_total),
                    'tax_price' => SetNumber($tax_price),
                    'tax_id_value' => $tax_id,
                    'final_total_amount' => SetNumber($total_amount + $tax_price),
                    'CURRENCY' => $CURRENCY,
                ];
            }
            return response()->json($return);
        } else {
            $response = Cart::cart_list_cookie($request->all(), $store->id);

            $response = json_decode(json_encode($response));

            if ($Product == null) {
                $tax_id = $request['tax_id_value'];
            } else {
                $tax_id = $Product->tax_id;
            }

            if ($plan->shipping_method != 'on') {
                $return = [
                    'sale_price' => SetNumber($response->data->sub_total),
                    'tax_price' => SetNumber($response->data->total_tax_price),
                    'tax_id_value' => $tax_id,
                    'final_total_amount' => SetNumber($response->data->total_sub_price),
                    'total_coupon_price' => SetNumber($response->data->total_coupon_price),
                    'CURRENCY' => $CURRENCY,
                ];
            } elseif ($plan->shipping_method == 'on') {
                $shipping = $request['shipping_final_price'] ? $request['shipping_final_price'] : (!empty($request->data->shipping_original_price) ? $response->data->shipping_original_price : 0);
                $coupon_amount = 0;
                if ($request['total_coupon_amount'] == '') {
                    $total_amount = $response->data->total_sub_price;
                } else {
                    $total_amount = ($response->data->total_sub_price);
                }

                $return = [
                    'sale_price' => SetNumber($response->data->sub_total),
                    'tax_id_value' => $tax_id,
                    'tax_price' => SetNumber($response->data->total_tax_price),
                    'final_total_amount' => SetNumber($total_amount),
                    'CURRENCY' => $CURRENCY,
                ];
            }
            return response()->json($return);
        }
    }

    public function abandon_carts_handled(AbandonCartDataTable $dataTable, Request $request)
    {
        if (auth()->user()->isAbleTo('Manage Cart')) {
            return $dataTable->render('cart.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function abandon_carts_show($cartId)
    {
        if (auth()->user()->isAbleTo('Show Cart')) {

            $cart = Cart::find($cartId);
            $cart_product = Cart::where('customer_id', $cart->customer_id)->where('theme_id', APP_THEME())->get();
            return view('cart.show', compact('cart_product'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function abandon_carts_destroy($cart_id)
    {
        if (auth()->user()->isAbleTo('Delete Cart')) {
            $store = Store::find(getCurrentStore());
            $carts = Cart::where('customer_id', $cart_id)->where('store_id',$store->id)->where('theme_id',$store->theme_id)->get();
            foreach ($carts as $cart) {
                $cart->delete();
            }

            return redirect()->back()->with('success', __('Cart delete successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function abandon_carts_emailsend(Request $request)
    {

        if (auth()->user()->isAbleTo('Abandon Cart')) {
            $cart  = Cart::find($request->cart_id);
            $user_id = $cart->customer_id;
            $cart_product = Cart::where('customer_id', $user_id)->where('theme_id', APP_THEME())->get();
            $email = $cart->UserData->email;

            $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
                return Store::where('id', getCurrentStore())->first();
            });
            $owner = User::find($store->created_by);
            $product_id    = Crypt::encrypt($cart->product_id);


            try {
                $dArr = Cart::where('customer_id', $user_id)->where('theme_id', APP_THEME())->get();

                $order_id = 1;
                $resp  = Utility::sendEmailTemplate('Abandon Cart', $email, $dArr, $owner, $store, $product_id, $user_id);



                // $return = 'Mail send successfully';
                if ($resp['is_success'] == false) {
                    return response()->json(
                        [
                            'is_success' => false,
                            'message' => $resp['error'],
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'is_success' => true,
                            'message' => __('Mail send successfully'),
                        ]
                    );
                }
            } catch (\Exception $e) {

                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                return response()->json(
                    [
                        'is_success' => false,
                        'message' => $smtp_error,
                    ]
                );
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function abandon_carts_messsend(Request $request)
    {
        $cart  = Cart::find($request->cart_id);
        $customer_id = $cart->customer_id;
        $mobile = $cart->UserData;
        if (auth()->user()->isAbleTo('Abandon Cart')) {

            try {
                $dArr = Cart::where('customer_id', $customer_id)->where('theme_id', APP_THEME())->pluck('product_id')->toArray();

                $product = [];
                foreach ($dArr as $item) {
                    $product[] = Product::where('id', $item)->pluck('name')->first();
                }
                $product_name = implode(',', $product);
                $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
                    return Store::where('id', getCurrentStore())->first();
                });
                $msg = __("We noticed that you recently visited our $store->name site and added some fantastic items to your shopping cart. We are thrilled that you found products you love! However, it seems like you did not finish your purchase.You finish your order process as soon as possible, Added Product name : $product_name");
                $resp  = Utility::SendMsgs('Abandon Cart', $mobile, $msg);

                // $return = 'Mail send successfully';
                if ($resp  == false) {
                    return response()->json(
                        [
                            'is_success' => false,
                            'message' => __("Invalid Auth access token - Cannot parse access token"),
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'is_success' => true,
                            'message' => __('Message send successfully'),
                        ]
                    );
                }
            } catch (\Exception $e) {

                $smtp_error = __('Invalid Auth access token - Cannot parse access token');
                return response()->json(
                    [
                        'is_success' => false,
                        'message' => $smtp_error,
                    ]
                );
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
