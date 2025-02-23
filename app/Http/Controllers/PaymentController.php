<?php

namespace App\Http\Controllers;

use App\Events\AddRewardClubPoint;
use App\Events\CustomerOrder;
use App\Events\GetProductStatus;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Cart;
use App\Models\City;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderTaxDetail;
use App\Models\OrderBillingDetail;
use App\Models\AppSetting;
use Stripe;
use App\Http\Controllers\CartController;
use Illuminate\Http\RedirectResponse;
use Session;
use App\Models\OrderNote;
use App\Models\OrderCouponDetail;
use App\Models\UserCoupon;
use App\Models\Customer;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
use App\Events\AddAdditionalFields;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponser;
use App\Http\Controllers\Api\ApiController;
use App\Models\ProductVariant;
use App\Models\TaxMethod;
use App\Facades\ModuleFacade as Module;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    use ApiResponser;
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processPayment(Request $request, $slug)
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        $theme_id = $store->theme_id;

        $user = Cache::remember('admin_details', 3600, function () {
            return User::where('type','admin')->first();
        });
        if ($user->type == 'admin') {
            $plan = Cache::remember('plan_details_'.$user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
        }

        $param['customer_id'] = auth('customers')->user()->id;
        $param['theme_id'] = $theme_id;
        $param['slug'] = $slug;
        $param['store_id'] = $store->id;
        if (isset($request->billing_info) && is_string($request->billing_info)) {
            $param['billing_info'] = (array) json_decode($request->billing_info);
            unset($request->billing_info);
        }

        if ($request->delivery_and_billing == 'on' || $request->delivery_and_billing == 'true' || $request->delivery_and_billing == true) {
            $param['billing_info']['firstname'] = $request->billing_info['firstname'];
            $param['billing_info']['lastname'] = $request->billing_info['lastname'];
            $param['billing_info']['email'] = $request->billing_info['email'];
            $param['billing_info']['billing_user_telephone'] = $request->billing_info['billing_user_telephone'];

            $param['billing_info']['billing_address'] = $request->billing_info['billing_address'];
            $param['billing_info']['billing_country'] = $request->billing_info['billing_country'];
            $param['billing_info']['billing_state'] = $request->billing_info['billing_state'];
            $param['billing_info']['billing_city'] = $request->billing_info['billing_city'];
            $param['billing_info']['billing_postecode'] = $request->billing_info['billing_postecode'];

            $param['billing_info']['delivery_address'] = $request->billing_info['billing_address'];
            $param['billing_info']['delivery_country'] = $request->billing_info['billing_country'];
            $param['billing_info']['delivery_state'] = $request->billing_info['billing_state'];
            $param['billing_info']['delivery_city'] = $request->billing_info['billing_city'];
            $param['billing_info']['delivery_postcode'] = $request->billing_info['billing_postecode'];
        }
        $request->merge($param);

        if ($request->payment_type == 'stripe' || $request->payment_type == 'paystack' || $request->payment_type == 'mercado' || $request->payment_type == 'skrill' || $request->payment_type == 'paymentwall' || $request->payment_type == 'Razorpay' || $request->payment_type == 'paypal' || $request->payment_type == 'flutterwave' || $request->payment_type == 'paytm' || $request->payment_type == 'mollie' || $request->payment_type == 'coingate' || $request->payment_type == 'toyyibpay' || $request->payment_type == 'Sspay' || $request->payment_type == 'Paytabs' || $request->payment_type == 'iyzipay' || $request->payment_type == 'payfast' || $request->payment_type == 'benefit' || $request->payment_type == 'cashfree' || $request->payment_type == 'aamarpay' || $request->payment_type == 'telegram' || $request->payment_type == 'whatsapp' || $request->payment_type == 'paytr' || $request->payment_type == 'yookassa' || $request->payment_type == 'Xendit' || $request->payment_type == 'midtrans' || $request->payment_type == 'cod' || $request->payment_type == 'bank_transfer' || $request->payment_type == 'Nepalste' || $request->payment_type == 'PayHere' || $request->payment_type == 'khalti' || $request->payment_type == 'AuthorizeNet' || $request->payment_type == 'Tap' || $request->payment_type == 'PhonePe' || $request->payment_type == 'Paddle' || $request->payment_type == 'Paiementpro' || $request->payment_type == 'FedPay' || $request->payment_type == 'CinetPay' || $request->payment_type == 'SenagePay' || $request->payment_type == 'CyberSource' || $request->payment_type == 'Ozow' || $request->payment_type == 'MyFatoorah' || $request->payment_type == 'easebuzz' || $request->payment_type == 'NMI' || $request->payment_type == 'payu' || $request->payment_type == 'Paynow' || $request->payment_type == 'esewa' || $request->payment_type == 'sofort' || $request->payment_type == 'DPO' || $request->payment_type == 'Braintree' || $request->payment_type == 'PowerTranz' || $request->payment_type == 'SSLCommerz') {

            $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
            if (module_is_active('PartialPayments')) {
                if (
                    $plan &&
                    (!isset($request->partial_payment_type) || $request->partial_payment_type != 'pending_payment')
                ) {
                    if (empty($billing['firstname'])) {
                        return redirect()->back()->with('error', __('Billing first name not found.'));
                    }
                    if (empty($billing['lastname'])) {
                        return redirect()->back()->with('error', __('Billing last name not found.'));
                    }
                    if (empty($billing['email'])) {
                        return redirect()->back()->with('error', __('Billing email not found.'));
                    }
                    if (empty($billing['billing_user_telephone'])) {
                        return redirect()->back()->with('error', __('Billing telephone not found.'));
                    }
                    if (empty($billing['billing_address'])) {
                        return redirect()->back()->with('error', __('Billing address not found.'));
                    }
                    // if (empty($billing['billing_postecode'])) {
                    //     return redirect()->back()->with('error', __('Billing postecode not found.'));
                    // }
                    if (empty($billing['billing_country'])) {
                        return redirect()->back()->with('error', __('Billing country not found.'));
                    }
                    if (empty($billing['billing_state'])) {
                        return redirect()->back()->with('error', __('Billing state not found.'));
                    }
                    if (empty($billing['billing_city'])) {
                        return redirect()->back()->with('error', __('Billing city not found.'));
                    }
                    if (empty($billing['delivery_address'])) {
                        return redirect()->back()->with('error', __('Delivery address not found.'));
                    }
                    // if (empty($billing['delivery_postcode'])) {
                    //     return redirect()->back()->with('error', __('Delivery postcode not found.'));
                    // }
                    if (empty($billing['delivery_country'])) {
                        return redirect()->back()->with('error', __('Delivery country not found.'));
                    }
                    if (empty($billing['delivery_state'])) {
                        return redirect()->back()->with('error', __('Delivery state not found.'));
                    }
                    if (empty($billing['delivery_city'])) {
                        return redirect()->back()->with('error', __('Delivery city not found.'));
                    }

                    $cartlist_final_price = 0;
                    $final_price = 0;
                    if (!auth('customers')->user()) {
                        $response = Cart::cart_list_cookie($request->all(), $store->id);
                        $response = json_decode(json_encode($response));
                        $cartlist = (array) $response->data;

                        if (empty($cartlist['product_list'])) {
                            return redirect()->back()->with('error', __('Cart is empty.'));
                        }

                        $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
                        $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
                        $final_price = $response->data->total_final_price;
                        $tax_price = !empty($cartlist['total_tax_price']) ? $cartlist['total_tax_price'] : '';
                        $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
                        $products = $cartlist['product_list'];
                    } elseif (!empty($request->customer_id) || auth('customers')->user()) {

                        $cart_list['customer_id'] = $request->customer_id ?? auth('customers')->user()->id;
                        $request->request->add($cart_list);

                        $cart_lists = new ApiController();
                        $cartlist_response = $cart_lists->cart_list($request, $slug);
                        $cartlist = (array) $cartlist_response->getData()->data;
                        if (empty($cartlist['product_list'])) {
                            return redirect()->back()->with('error', 'Cart is empty.');
                        }

                        if (module_is_active('CartQuantityControl')) {
                            $responseQuantityControl = \Workdo\CartQuantityControl\app\Models\CartQuantityControl::minQtyCheckoutValidation($cartlist);
                            if (isset($responseQuantityControl) && !empty($responseQuantityControl)) {

                                $out_of_stock_Message = isset($responseQuantityControl['out_of_stock']) ? implode('<br>', $responseQuantityControl['out_of_stock']) : '';
                                $min_quantity_Message = isset($responseQuantityControl['min_quantity']) ? implode(' , ', $responseQuantityControl['min_quantity']) : '';
                                $max_quantity_Message = isset($responseQuantityControl['max_quantity']) ? implode(' , ', $responseQuantityControl['max_quantity']) : '';

                                if (!empty($out_of_stock_Message) && !empty($min_quantity_Message)) {
                                    return redirect()->back()->with('error', 'The minimum allowed order quantity for <b>' . $min_quantity_Message . '</b> Please add more quantity. <br>' . $out_of_stock_Message);
                                } elseif (!empty($out_of_stock_Message)) {
                                    return redirect()->back()->with('error', $out_of_stock_Message);
                                } elseif (!empty($min_quantity_Message)) {
                                    return redirect()->back()->with('error', 'The minimum allowed order quantity for <b>' . $min_quantity_Message . '</b> Please add more quantity.');
                                } elseif (!empty($max_quantity_Message)) {
                                    return redirect()->back()->with('error', 'The maximum allowed order quantity for <b>' . $max_quantity_Message . '</b> Please remove some quantity.');
                                }
                            }
                        }

                        if (module_is_active('RewardClubPoint') && isset($request->club_point_is_active) && $request->club_point_is_active == 'on') {
                            $customerDetail = Customer::find($request->customer_id);
                            $settings = getAdminAllSetting($store->created_by, $store->id, $store->theme_id) ?? null;
                            $saveRewardPrice = ($customerDetail->total_club_point * $settings['reward_point_price'] ?? 0) / ($settings['reward_points'] ?? 0);
                            $cartlist_final_price = (!empty($cartlist['final_price']) ? $cartlist['final_price'] : 0) - $saveRewardPrice;
                        } else {
                            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
                        }
                        $final_price = $cartlist['total_final_price'];
                        $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
                        $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
                        $tax_price = !empty($cartlist['total_tax_price']) ? $cartlist['total_tax_price'] : '';

                        $products = $cartlist['product_list'];
                    } else {
                        return redirect()->back()->with('error', 'User not found.');
                    }
                }
            } else {
                if (empty($billing['firstname'])) {
                    return redirect()->back()->with('error', __('Billing first name not found.'));
                }
                if (empty($billing['lastname'])) {
                    return redirect()->back()->with('error', __('Billing last name not found.'));
                }
                if (empty($billing['email'])) {
                    return redirect()->back()->with('error', __('Billing email not found.'));
                }
                if (empty($billing['billing_user_telephone'])) {
                    return redirect()->back()->with('error', __('Billing telephone not found.'));
                }
                if (empty($billing['billing_address'])) {
                    return redirect()->back()->with('error', __('Billing address not found.'));
                }
                // if (empty($billing['billing_postecode'])) {
                //     return redirect()->back()->with('error', __('Billing postecode not found.'));
                // }
                if (empty($billing['billing_country'])) {
                    return redirect()->back()->with('error', __('Billing country not found.'));
                }
                if (empty($billing['billing_state'])) {
                    return redirect()->back()->with('error', __('Billing state not found.'));
                }
                if (empty($billing['billing_city'])) {
                    return redirect()->back()->with('error', __('Billing city not found.'));
                }
                if (empty($billing['delivery_address'])) {
                    return redirect()->back()->with('error', __('Delivery address not found.'));
                }
                // if (empty($billing['delivery_postcode'])) {
                //     return redirect()->back()->with('error', __('Delivery postcode not found.'));
                // }
                if (empty($billing['delivery_country'])) {
                    return redirect()->back()->with('error', __('Delivery country not found.'));
                }
                if (empty($billing['delivery_state'])) {
                    return redirect()->back()->with('error', __('Delivery state not found.'));
                }
                if (empty($billing['delivery_city'])) {
                    return redirect()->back()->with('error', __('Delivery city not found.'));
                }

                $cartlist_final_price = 0;
                $final_price = 0;
                if (!auth('customers')->user()) {
                    $response = Cart::cart_list_cookie($request->all(), $store->id);
                    $response = json_decode(json_encode($response));
                    $cartlist = (array) $response->data;

                    if (empty($cartlist['product_list'])) {
                        return redirect()->back()->with('error', 'Cart is empty.');
                    }

                    $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
                    $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
                    $final_price = $response->data->total_final_price;
                    $tax_price = !empty($cartlist['total_tax_price']) ? $cartlist['total_tax_price'] : '';
                    $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
                    $products = $cartlist['product_list'];
                } elseif (!empty($request->customer_id) || auth('customers')->user()) {

                    $cart_list['customer_id'] = $request->customer_id ?? auth('customers')->user()->id;
                    $request->request->add($cart_list);

                    $cart_lists = new ApiController();
                    $cartlist_response = $cart_lists->cart_list($request, $slug);
                    $cartlist = (array) $cartlist_response->getData()->data;
                    if (empty($cartlist['product_list'])) {
                        return redirect()->back()->with('error', 'Cart is empty.');
                    }

                    if (module_is_active('CartQuantityControl')) {
                        $responseQuantityControl = \Workdo\CartQuantityControl\app\Models\CartQuantityControl::minQtyCheckoutValidation($cartlist);
                        if (isset($responseQuantityControl) && !empty($responseQuantityControl)) {

                            $out_of_stock_Message = isset($responseQuantityControl['out_of_stock']) ? implode('<br>', $responseQuantityControl['out_of_stock']) : '';
                            $min_quantity_Message = isset($responseQuantityControl['min_quantity']) ? implode(' , ', $responseQuantityControl['min_quantity']) : '';
                            $max_quantity_Message = isset($responseQuantityControl['max_quantity']) ? implode(' , ', $responseQuantityControl['max_quantity']) : '';

                            if (!empty($out_of_stock_Message) && !empty($min_quantity_Message)) {
                                return redirect()->back()->with('error', 'The minimum allowed order quantity for <b>' . $min_quantity_Message . '</b> Please add more quantity. <br>' . $out_of_stock_Message);
                            } elseif (!empty($out_of_stock_Message)) {
                                return redirect()->back()->with('error', $out_of_stock_Message);
                            } elseif (!empty($min_quantity_Message)) {
                                return redirect()->back()->with('error', 'The minimum allowed order quantity for <b>' . $min_quantity_Message . '</b> Please add more quantity.');
                            } elseif (!empty($max_quantity_Message)) {
                                return redirect()->back()->with('error', 'The maximum allowed order quantity for <b>' . $max_quantity_Message . '</b> Please remove some quantity.');
                            }
                        }
                    }
                    if (module_is_active('RewardClubPoint') && isset($request->club_point_is_active) && $request->club_point_is_active == 'on') {

                        $customerDetail = Customer::find($request->customer_id);
                        $settings = getAdminAllSetting($store->created_by, $store->id, $store->theme_id) ?? null;
                        $saveRewardPrice = ($customerDetail->total_club_point * $settings['reward_point_price'] ?? 0) / ($settings['reward_points'] ?? 0);
                        $cartlist_final_price = (!empty($cartlist['final_price']) ? $cartlist['final_price'] : 0) - $saveRewardPrice;
                    } else {
                        $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
                    }
                    $final_price = $cartlist['total_final_price'];
                    $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
                    $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
                    $tax_price = !empty($cartlist['total_tax_price']) ? $cartlist['total_tax_price'] : '';

                    $products = $cartlist['product_list'];
                } else {
                    return redirect()->back()->with('error', 'User not found.');
                }
            }

            $coupon_price = 0;
            // coupon api call
            if (!empty($request['coupon_code'])) {
                if (isset($request['coupon_info']) && $request['coupon_info']) {
                    $coupon_price = $request['coupon_info']['coupon_discount_amount'] ?? 0;
                } else {
                    //$coupon_data = $request->coupon_info;
                    $apply_coupon = [
                        'coupon_code' => $request['coupon_code'] ?? null,
                        'sub_total' => $cartlist_final_price ?? 0
                    ];
                    $request->request->add($apply_coupon);
                    $coupon_apply = new ApiController();
                    $apply_coupon_response = $coupon_apply->apply_coupon($request, $slug);
                    $apply_coupon = (array) $apply_coupon_response->getData()->data;

                    $order_array['coupon']['message'] = $apply_coupon['message'];
                    $order_array['coupon']['status'] = false;
                    if (!empty($apply_coupon['final_price'])) {
                        $cartlist_final_price = $apply_coupon['final_price'];
                        $coupon_price = $apply_coupon['amount'];
                        $order_array['coupon']['status'] = true;
                    }
                }
            }
            if (module_is_active('PartialPayments')) {
                if (
                    $plan &&
                    (!isset($request->partial_payment_type) || $request->partial_payment_type != 'pending_payment')
                ) {
                    // dilivery api call
                    $delivery_price = 0;
                    if ($plan->shipping_method == 'on') {
                        if (!empty($request->method_id)) {
                            $del_charge = new CartController();
                            $delivery_charge = $del_charge->get_shipping_method($request, $slug);
                            $content = $delivery_charge->getContent();
                            $data = json_decode($content, true);

                            $delivery_price = $data['total_final_price'];
                            $tax_price = $data['final_tax_price'];
                        } 
                        // else {
                        //     return redirect()->back()->with('error', 'Shipping Method not found!');
                        //     // return $this->error(['message' => 'Shipping Method not found']);
                        // }
                    } else {
                        if (!empty($tax_price)) {
                            $tax_price = $tax_price;
                        } else {
                            $tax_price = 0;
                        }
                    }
                    $new_array['shipping_final_price'] = $delivery_price;
                    $new_array['tax_price'] = $tax_price;
                    $request->merge($new_array);

                    if (!empty($prodduct_id_array)) {
                        $prodduct_id_array = $prodduct_id_array = array_unique($prodduct_id_array);
                        $prodduct_id_array = implode(',', $prodduct_id_array);
                    } else {
                        $prodduct_id_array = '';
                    }
                    $new_array['cartlist_final_price'] = $cartlist_final_price + $delivery_price + $tax_price;

                    $new_array['cartlist'] = $cartlist;
                    $request->merge($new_array);
                }
            } else {
                // dilivery api call
                $delivery_price = 0;
                if ($plan->shipping_method == 'on') {
                    if (!empty($request->method_id)) {
                        $del_charge = new CartController();
                        $delivery_charge = $del_charge->get_shipping_method($request, $slug);
                        $content = $delivery_charge->getContent();
                        $data = json_decode($content, true);

                        $delivery_price = $data['total_final_price'];
                        $tax_price = $data['final_tax_price'];
                    } 
                    // else {
                    //     return redirect()->back()->with('error', __('Shipping Method not found!'));
                    //     // return $this->error(['message' => 'Shipping Method not found']);
                    // }
                } else {
                    if (!empty($tax_price)) {
                        $tax_price = $tax_price;
                    } else {
                        $tax_price = 0;
                    }
                }
                $new_array['shipping_final_price'] = $delivery_price;
                $new_array['tax_price'] = $tax_price;
                $request->merge($new_array);

                if (!empty($prodduct_id_array)) {
                    $prodduct_id_array = $prodduct_id_array = array_unique($prodduct_id_array);
                    $prodduct_id_array = implode(',', $prodduct_id_array);
                } else {
                    $prodduct_id_array = '';
                }
                $new_array['cartlist_final_price'] = $cartlist_final_price + $delivery_price + $tax_price;

                $new_array['cartlist'] = $cartlist;
                $request->merge($new_array);
            }
            $product_reward_point = 1;

            $new_array['billing_info'] = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;

            if (module_is_active('PartialPayments')) {
                $settings = Setting::where('theme_id', $store->theme_id)->where('store_id', $store->id)->pluck('value', 'name')->toArray();
                if ((isset($settings['enable_partial_payment']) && $settings['enable_partial_payment'] == 'on') && \Auth::guard('customers')->user()) {
                    $param = [
                        'tax_price' => $new_array['tax_price'] ?? 0,
                        'shipping_price' => $new_array['shipping_final_price'] ?? 0,
                        'coupon_price' => $new_array['cartlist']['total_coupon_price'] ?? 0,
                    ];
                    $request->merge($param);
                    $updated_cart = \Workdo\PartialPayments\app\Http\Controllers\PartialPaymentsController::ManageDeposit($request, $store->slug);
                    if ((!isset($request->partial_payment_type) || $request->partial_payment_type != 'pending_payment')) {
                        $new_array['cartlist_final_price'] = $updated_cart['deposite_amount'] ?? 0;
                        $new_array['shipping_final_price'] = $updated_cart['shipping_price_value'] ?? $new_array['shipping_final_price'];
                        $new_array['tax_price'] = $updated_cart['tax_price_value'] ?? $new_array['tax_price'];
                        $new_array['shipping_price'] = $updated_cart['shipping_price_value'] ?? $new_array['shipping_final_price'];
                        $new_array['coupon_price'] = $updated_cart['coupon_price_value'] ?? $new_array['cartlist']['total_coupon_price'];
                        $new_array['order_pending_amount'] = $updated_cart['pending_amount'] ?? 0;
                    } else {
                        $cartlist = [];
                        $new_array['cartlist_final_price'] = $request->payment_amount ?? 0;
                    }

                }

            }
            $request->merge($new_array);
            $paymentMethod = $request->payment_type;
            $response = $this->paymentService->process($request, $paymentMethod, $slug, $cartlist);
            if ($request->payment_type == 'paystack' || $request->payment_type == 'Razorpay' || $request->payment_type == 'paymentwall' || $request->payment_type == 'flutterwave' || $request->payment_type == 'paytm' || $request->payment_type == 'telegram' || $request->payment_type == 'paytr' || $request->payment_type == 'midtrans' || $request->payment_type == 'whatsapp' || $request->payment_type == 'PayHere' || $request->payment_type == 'khalti' || $request->payment_type == 'AuthorizeNet') {
                $viewHtml = $response->render();
                return $viewHtml;
            } elseif ($request->payment_type == 'skrill' || $request->payment_type == 'Sspay' || $request->payment_type == 'toyyibpay' || $request->payment_type == 'Paytabs' || $request->payment_type == 'cod' || $request->payment_type == 'bank_transfer' || $request->payment_type == 'Nepalste' || $request->payment_type == 'Tap' || $request->payment_type == 'PhonePe' || $request->payment_type == 'Paddle' || $request->payment_type == 'Paiementpro' || $request->payment_type == 'FedPay' || $request->payment_type == 'CinetPay' || $request->payment_type == 'SenagePay' || $request->payment_type == 'Ozow' || $request->payment_type == 'easebuzz' || $request->payment_type == 'esewa' || $request->payment_type == 'Paynow' || $request->payment_type == 'DPO' || $request->payment_type == 'MyFatoorah') {
                return new RedirectResponse($response->getTargetUrl());
            } elseif ($request->payment_type == 'coingate') {
                return new RedirectResponse($response->payment_url);
            } elseif ($request->payment_type == 'payfast' || $request->payment_type == 'CyberSource' || $request->payment_type == 'NMI' || $request->payment_type == 'payu' || $request->payment_type == 'Braintree' || $request->payment_type == 'PowerTranz' || $request->payment_type == 'SSLCommerz') {
                return $response;
            } else {
                return new RedirectResponse($response);
            }

        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }

    }

    public function getProductStatus(Request $request, $slug)
    {
        $requests_data = (Session::get('request_data'));
        if($requests_data['payment_type'] == 'toyyibpay')
        {
            if ($request->status_id == 3)
            {
                return redirect()->route('checkout', $slug)->with('error', __('Your Transaction is fail please try again.'));
            }else if ($request->status_id == 2)
            {  
                return redirect()->route('checkout', $slug)->with('success', __('Your transaction on pending'));
            }
        }

        if (is_array($requests_data)) {
            // Merge $requests_data into the request
            $request->merge($requests_data);
        }

        if (!isset($requests_data['coupon_info']) && isset($request->cartlist['coupon_info'])) {
            $requests_data['coupon_info'] = is_object($request->cartlist['coupon_info']) ? (array) $request->cartlist['coupon_info'] : $request->cartlist['coupon_info'];
        }

        if ($requests_data == null) {
            $requests_data = $request->all();
        }
        $slug = !empty($requests_data['slug']) ? $requests_data['slug'] : '';
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        Session::forget('request_data');
        $customer_id = $requests_data['customer_id'] ?? '';

        if (!empty($requests_data['method_id'])) {

            $request['method_id'] = $requests_data['method_id'];
        }
        $user = Cache::remember('admin_details', 3600, function () {
            return User::where('type','admin')->first();
        });
        if ($user->type == 'admin') {
            $plan = Cache::remember('plan_details_'.$user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
        }
        $theme_id = !empty($store->theme_id) ? $store->theme_id : ($requests_data['theme_id'] ?? APP_THEME());
        if (module_is_active('PartialPayments')) {
            if ($plan && strpos($plan->modules, 'PartialPayments') !== false && \Auth::guard('customers')->user()) {
                if ((isset($request->partial_payment_type) || $request->partial_payment_type == 'pending_payment')) {
                    $return = \Workdo\PartialPayments\app\Models\PartialPayments::ManagePartialPayment($requests_data, $slug);
                    if ($return['status'] == 'success') {
                        return redirect()->back()->with('success', __($return['message']));
                    }
                }
            }
        }
        if (!auth('customers')->user()) {
            if ($request->coupon_code != null) {
                $coupon = Coupon::where('id', $request->coupon_info['coupon_id'])->where('store_id', $store->id)->where('theme_id', $theme_id)->first();
                $coupon_email = $coupon->PerUsesCouponCount();
                $i = 0;
                foreach ($coupon_email as $email) {
                    if ($email == $request->billing_info['email']) {
                        $i++;
                    }
                }

                if (!empty($coupon->coupon_limit_user)) {
                    if ($i >= $coupon->coupon_limit_user) {
                        return $this->error(['message' => 'This coupon has expired.']);
                    }
                }
            }
        }
        if (!auth('customers')->user()) {
            $rules = [
                'billing_info' => 'required',
                'payment_type' => 'required',
                //'delivery_id' => 'required',
            ];
        } else {
            $rules = [
                'customer_id' => 'required',
                'billing_info' => 'required',
                'payment_type' => 'required',
                //'delivery_id' => 'required',
            ];
        }

        $validator = \Validator::make($requests_data, $rules);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            Utility::error([
                'message' => $messages->first()
            ]);
        }

        $cartlist_final_price = 0;
        $final_price = 0;
        $tax_price = 0;
        // cart list api
        if (!auth('customers')->user()) {
            $response = Cart::cart_list_cookie($requests_data, $store->id ?? '');

            $response = json_decode(json_encode($response));
            $cartlist = (array) $response->data;
            if (empty($cartlist['product_list'])) {
                return $this->error(['message' => 'Cart is empty.']);
            }
            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
            $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
            $final_price = $response->data->total_final_price;
            $tax_price = !empty($requests_data['tax_price']) ? $requests_data['tax_price'] : '';
            $billing = $requests_data['billing_info'];

            $products = $cartlist['product_list'];
        } elseif (!empty($customer_id)) {
            $cart_list['customer_id'] = $customer_id;
            if (is_array($requests_data)) {
                // Merge $requests_data into the request
                $request->merge($requests_data);
            }
            $cartt = new ApiController();
            $cartlist_response = $cartt->cart_list($request, $slug);
            $cartlist = (array) $cartlist_response->getData()->data;
            if (empty($cartlist['product_list'])) {
                return $this->error(['message' => 'Cart is empty.']);
            }

            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
            $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
            $final_price = $cartlist['total_final_price'];
            $tax_price = !empty($requests_data['tax_price']) ? $requests_data['tax_price'] : '';
            $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
            $products = $cartlist['product_list'];
        } else {
            return Utility::error(['message' => 'User not found.']);
        }

        $coupon_price = 0;
        // coupon api call
        if (!empty($requests_data['coupon_info'])) {
            $coupon_data = $requests_data['coupon_info'];
            $apply_coupon = [
                'coupon_code' => $coupon_data['coupon_code'],
                'sub_total' => $cartlist_final_price,
                'theme_id' => $requests_data['theme_id'],
                'slug' => $requests_data['slug']

            ];
            $request->merge($apply_coupon);
            $couponss = new ApiController();
            $apply_coupon_response = $couponss->apply_coupon($request, $slug);
            $apply_coupon = (array) $apply_coupon_response->getData()->data;

            $order_array['coupon']['message'] = $apply_coupon['message'];
            $order_array['coupon']['status'] = false;
            if (!empty($apply_coupon['final_price'])) {
                $cartlist_final_price = $apply_coupon['final_price'];
                $coupon_price = $apply_coupon['amount'];
                $order_array['coupon']['status'] = true;
            }
        } elseif (!empty($requests_data['cartlist']['coupon_info'])) {
            $coupon_data = is_object($requests_data['cartlist']['coupon_info']) ? (array) $requests_data['cartlist']['coupon_info'] : $requests_data['cartlist']['coupon_info'];

            $apply_coupon = [
                'coupon_code' => $coupon_data['coupon_code'],
                'sub_total' => $cartlist_final_price,
                'theme_id' => $requests_data['theme_id'],
                'slug' => $requests_data['slug']

            ];
            $request->merge($apply_coupon);
            $couponss = new ApiController();
            $apply_coupon_response = $couponss->apply_coupon($request, $slug);
            $apply_coupon = (array) $apply_coupon_response->getData()->data;
            $order_array['coupon']['message'] = $apply_coupon['message'];
            $order_array['coupon']['status'] = false;
            if (!empty($apply_coupon['final_price'])) {
                $cartlist_final_price = $apply_coupon['final_price'];
                $coupon_price = $apply_coupon['amount'];
                $order_array['coupon']['status'] = true;
            }
        }

        $delivery_price = 0;
        if ($plan->shipping_method == 'on') {
            if (!empty($request->method_id)) {
                $del_charge = new CartController();
                $delivery_charge = $del_charge->get_shipping_method($request, $slug);
                $content = $delivery_charge->getContent();
                $data = json_decode($content, true);
                $delivery_price = $data['total_final_price'];
                $tax_price = $requests_data['tax_price'] ?? 0;
            } else {
                return $this->error(['message' => 'Shipping Method not found']);
            }
        } else {
            if (!empty($tax_price)) {
                $tax_price = $tax_price;
            } else {
                $tax_price = 0;
            }
        }

        $settings = Setting::where('theme_id', $theme_id)->where('store_id', $store->id)->pluck('value', 'name')->toArray();

        // Order stock decrease start
        $prodduct_id_array = [];
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $prodduct_id_array[] = $product->product_id;

                $product_id = $product->product_id;
                $variant_id = $product->variant_id;
                $qtyy = !empty($product->qty) ? $product->qty : 0;

                $Product = Product::where('id', $product_id)->first();
                $datas = Product::find($product_id);
                if ($settings['stock_management'] ?? '' == 'on') {
                    if (!empty($product_id) && !empty($variant_id) && $product_id != 0 && $variant_id != 0) {
                        $ProductStock = ProductVariant::where('id', $variant_id)->where('product_id', $product_id)->first();
                        $variationOptions = explode(',', $ProductStock->variation_option);
                        $option = in_array('manage_stock', $variationOptions);
                        if (!empty($ProductStock)) {
                            if ($option == true) {
                                $remain_stock = $ProductStock->stock - $qtyy;
                                $ProductStock->stock = $remain_stock;
                                $ProductStock->save();

                                if ($ProductStock->stock <= $ProductStock->low_stock_threshold) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_low_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_low_stock_threshold($product, $ProductStock, $theme_id, $settings);
                                        }

                                    }
                                }
                                if ($ProductStock->stock <= $settings['out_of_stock_threshold']) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_out_of_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_out_of_stock($product, $ProductStock, $theme_id, $settings);
                                        }
                                    }
                                }
                            } else {
                                $remain_stock = $datas->product_stock - $qtyy;
                                $datas->product_stock = $remain_stock;
                                $datas->save();
                                if ($datas->product_stock <= $datas->low_stock_threshold) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_low_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_low_stock_threshold($product, $datas, $theme_id, $settings);
                                        }

                                    }
                                }
                                if ($datas->product_stock <= $settings['out_of_stock_threshold']) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_out_of_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_out_of_stock($product, $datas, $theme_id, $settings);
                                        }
                                    }
                                }
                                if ($datas->product_stock <= $settings['out_of_stock_threshold'] && $datas->stock_order_status == 'notify_customer') {
                                    //Stock Mail
                                    $order_email = $billing['email'];
                                    $owner = User::find($store->created_by);
                                    $ProductId = '';

                                    try {
                                        $dArr = [
                                            'item_variable' => $Product->id,
                                            'product_name' => $Product->name,
                                            'customer_name' => $billing['firstname'],
                                        ];

                                        // Send Email
                                        $resp = Utility::sendEmailTemplate('Stock Status', $order_email, $dArr, $owner, $store, $ProductId);
                                    } catch (\Exception $e) {
                                        $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                                    }
                                    try {
                                        $mobile_no = $request['billing_info']['billing_user_telephone'];
                                        $customer_name = $request['billing_info']['firstname'];
                                        $msg = __("Dear,$customer_name .Hi,We are excited to inform you that the product you have been waiting for is now back in stock.Product Name: :$Product->name. ");
                                        $resp = Utility::SendMsgs('Stock Status', $mobile_no, $msg);
                                    } catch (\Exception $e) {
                                        $smtp_error = __('Invalid OAuth access token - Cannot parse access token');
                                    }
                                }
                            }
                        } else {
                            return $this->error(['message' => 'Product not found .']);
                        }
                    } elseif (!empty($product_id) && $product_id != 0) {

                        if (!empty($Product)) {
                            $remain_stock = $Product->product_stock - $qtyy;
                            $Product->product_stock = $remain_stock;
                            $Product->save();
                            if ($Product->product_stock <= $Product->low_stock_threshold) {
                                if (!empty(json_decode($settings['notification'])) && in_array("enable_low_stock", json_decode($settings['notification']))) {
                                    if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                        Utility::low_stock_threshold($Product, $theme_id, $settings);
                                    }
                                }
                            }

                            if ($Product->product_stock <= $settings['out_of_stock_threshold']) {
                                if (!empty(json_decode($settings['notification'])) && in_array("enable_out_of_stock", json_decode($settings['notification']))) {
                                    if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                        Utility::out_of_stock($Product, $theme_id, $settings);
                                    }
                                }
                            }

                            if ($Product->product_stock <= $settings['out_of_stock_threshold'] && $Product->stock_order_status == 'notify_customer') {
                                //Stock Mail
                                $order_email = $billing['email'];
                                $owner = User::find($store->created_by);
                                $ProductId = '';

                                try {
                                    $dArr = [
                                        'item_variable' => $Product->id,
                                        'product_name' => $Product->name,
                                        'customer_name' => $billing['firstname'],
                                    ];

                                    // Send Email
                                    $resp = Utility::sendEmailTemplate('Stock Status', $order_email, $dArr, $owner, $store, $ProductId);
                                } catch (\Exception $e) {
                                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                                }
                                try {
                                    $mobile_no = $request['billing_info']['billing_user_telephone'];
                                    $customer_name = $request['billing_info']['firstname'];
                                    $msg = __("Dear,$customer_name .Hi,We are excited to inform you that the product you have been waiting for is now back in stock.Product Name: :$Product->name. ");
                                    $resp = Utility::SendMsgs('Stock Status', $mobile_no, $msg);
                                } catch (\Exception $e) {
                                    $smtp_error = __('Invalid OAuth access token - Cannot parse access token');
                                }
                            }

                        } else {
                            return $this->error(['message' => 'Product not found .']);
                        }
                    } else {
                        return $this->error(['message' => 'Please fill proper product json field .']);
                    }
                }
                // remove from cart
                Cart::where('customer_id', $request->customer_id)->where('product_id', $product_id)->where('variant_id', $variant_id)->where('theme_id', $theme_id)->where('store_id', $store->id)->delete();
            }
        }
        if (isset($request->customer_id) && !empty($request->customer_id)) {
            // Cart::where('customer_id', $request->customer_id)->delete();
        }
        // Order stock decrease end
        if (!empty($prodduct_id_array)) {
            $prodduct_id_array = $prodduct_id_array = array_unique($prodduct_id_array);
            $prodduct_id_array = implode(',', $prodduct_id_array);
        } else {
            $prodduct_id_array = '';
        }

        $product_reward_point = 1;

        $product_order_id = '0' . date('YmdHis');
        $is_guest = 1;
        if (auth('customers')->check()) {
            $product_order_id = $request->customer_id . date('YmdHis');
            $is_guest = 0;
        }

        // add in  Order table  start
        $order = new Order();
        $order->product_order_id = $product_order_id;
        $order->order_date = date('Y-m-d H:i:s');
        $order->customer_id = !empty($request->customer_id) ? $request->customer_id : 0;
        $order->is_guest = $is_guest;
        $order->product_id = $prodduct_id_array;
        $order->product_json = json_encode($products);
        $order->product_price = $final_price;
        $order->coupon_price = $coupon_price;
        $order->delivery_price = $delivery_price;
        $order->tax_price = $tax_price;
        if (!auth('customers')->user()) {
            if ($plan->shipping_method == "on") {
                $order->final_price = $final_sub_total_price;
            } else {
                $order->final_price = $final_price + $tax_price - $coupon_price;
            }
        } elseif (module_is_active('PartialPayments') && \Auth::guard('customers')->user() && (isset($settings['enable_partial_payment']) && $settings['enable_partial_payment'] == 'on')) {
            $order->final_price = $requests_data['cartlist_final_price'] ?? $total_sub_price;
            $order->delivery_price = $requests_data['shipping_price'] ?? $delivery_price;
            \Workdo\PartialPayments\app\Models\OrderPartialPayments::OrderPartialPayments($order, $slug, $request);
        } else {
            if (module_is_active('RewardClubPoint') && isset($requests_data['club_point_is_active']) && $requests_data['club_point_is_active'] == 'on') {
                $customerDetail = Customer::find($customer_id);
                $rewardSetting = getAdminAllSetting($store->created_by, $store->id, $store->theme_id) ?? null;
                $saveRewardPrice = ($customerDetail->total_club_point * $rewardSetting['reward_point_price'] ?? 0) / ($rewardSetting['reward_points'] ?? 0);
                if ($plan->shipping_method == "on") {
                    $order->final_price = $final_sub_total_price - $saveRewardPrice;
                } else {
                    $order->final_price = $final_price + $tax_price - $saveRewardPrice;
                }
            } else {
                if ($plan->shipping_method == "on") {
                    $order->final_price = $final_sub_total_price;
                } else {
                    $order->final_price = $final_price + $tax_price - $coupon_price;
                }
            }
        }
        event(new AddAdditionalFields($order, $request->all(), $store));
        $order->payment_comment = !empty($requests_data['payment_comment']) ? $requests_data['payment_comment'] : '';
        $order->payment_type = $requests_data['payment_type'];
        $order->payment_status = 'Paid';
        $order->delivery_id = $requests_data['method_id'] ?? 0;
        $order->delivery_comment = !empty($requests_data['delivery_comment']) ? $requests_data['delivery_comment'] : '';
        if (module_is_active('PreOrder') && \Auth::guard('customers')->user() && isset($requests_data['order_type']) && $requests_data['order_type'] == 'pre_order') {
            $order->delivered_status = 8;
        }else{
            $order->delivered_status = 0;
        }
        $order->reward_points = SetNumber($product_reward_point);
        $order->additional_note = $request->additional_note;
        $order->theme_id = $store->theme_id;
        $order->store_id = $store->id;
        $order->save();
        // add in  Order table end
        if (module_is_active('CheckoutAttachment')) {
            \Workdo\CheckoutAttachment\app\Models\CheckoutAttachment::CheckoutAttachment($order, $slug, $request);
        }
        // Utility::paymentWebhook($order);

        $billing_city_id = $billing['billing_city'] ?? 0;
        if (!empty($billing['billing_city'])) {
            $cityy = City::where('id', $billing['billing_city'])->first();
            if (!empty($cityy)) {
                $billing_city_id = $cityy->id;
            } else {
                $new_billing_city = new City();
                $new_billing_city->name = $billing['billing_city'];
                $new_billing_city->state_id = $billing['billing_state'];
                $new_billing_city->country_id = $billing['billing_country'];
                $new_billing_city->save();
                $billing_city_id = $new_billing_city->id;
            }
        }

        $delivery_city_id = $billing['delivery_city'] ?? 0;
        if (!empty($billing['delivery_city'])) {
            $d_cityy = City::where('id', $billing['delivery_city'])->first();
            if (!empty($d_cityy)) {
                $delivery_city_id = $d_cityy->id;
            } else {
                $new_delivery_city = new City();
                $new_delivery_city->name = $billing['delivery_city'];
                $new_delivery_city->state_id = $billing['delivery_state'];
                $new_delivery_city->country_id = $billing['delivery_country'];
                $new_delivery_city->save();
                $delivery_city_id = $new_delivery_city->id;
            }
        }

        $OrderBillingDetail = new OrderBillingDetail();
        $OrderBillingDetail->order_id = $order->id;
        $OrderBillingDetail->product_order_id = $order->product_order_id;
        $OrderBillingDetail->first_name = !empty($billing['firstname']) ? $billing['firstname'] : '';
        $OrderBillingDetail->last_name = !empty($billing['lastname']) ? $billing['lastname'] : '';
        $OrderBillingDetail->email = !empty($billing['email']) ? $billing['email'] : '';
        $OrderBillingDetail->telephone = !empty($billing['billing_user_telephone']) ? $billing['billing_user_telephone'] : '';
        $OrderBillingDetail->address = !empty($billing['billing_address']) ? $billing['billing_address'] : '';
        $OrderBillingDetail->postcode = !empty($billing['billing_postecode']) ? $billing['billing_postecode'] : '';
        $OrderBillingDetail->country = !empty($billing['billing_country']) ? $billing['billing_country'] : '';
        $OrderBillingDetail->state = !empty($billing['billing_state']) ? $billing['billing_state'] : '';
        $OrderBillingDetail->city = $billing_city_id;
        $OrderBillingDetail->theme_id = $theme_id;
        $OrderBillingDetail->delivery_address = !empty($billing['delivery_address']) ? $billing['delivery_address'] : '';
        $OrderBillingDetail->delivery_city = $delivery_city_id;
        $OrderBillingDetail->delivery_postcode = !empty($billing['delivery_postcode']) ? $billing['delivery_postcode'] : '';
        $OrderBillingDetail->delivery_country = !empty($billing['delivery_country']) ? $billing['delivery_country'] : '';
        $OrderBillingDetail->delivery_state = !empty($billing['delivery_state']) ? $billing['delivery_state'] : '';
        $OrderBillingDetail->save();

        // add in Order Coupon Detail table start
        if (!empty($requests_data['coupon_info'])) {
            $coupon_data = $requests_data['coupon_info'];
            $Coupon = Coupon::find($coupon_data['coupon_id']);
            // coupon stock decrease end

            // Order Coupon history
            $OrderCouponDetail = new OrderCouponDetail();
            $OrderCouponDetail->order_id = $order->id;
            $OrderCouponDetail->product_order_id = $order->product_order_id;
            $OrderCouponDetail->coupon_id = $coupon_data['coupon_id'];
            $OrderCouponDetail->coupon_name = $coupon_data['coupon_name'];
            $OrderCouponDetail->coupon_code = $coupon_data['coupon_code'];
            $OrderCouponDetail->coupon_discount_type = $coupon_data['coupon_discount_type'];
            $OrderCouponDetail->coupon_discount_number = $coupon_data['coupon_discount_number'];
            $OrderCouponDetail->coupon_discount_amount = $coupon_data['coupon_discount_amount'];
            $OrderCouponDetail->coupon_final_amount = $coupon_data['coupon_final_amount'];
            $OrderCouponDetail->theme_id = $theme_id;
            $OrderCouponDetail->save();

            // Coupon history
            if (isset($Coupon)) {
                $UserCoupon = new UserCoupon();
                $UserCoupon->user_id = !empty($requests_data['customer_id']) ? $requests_data['customer_id'] : null;
                $UserCoupon->coupon_id = $Coupon->id;
                $UserCoupon->amount = $coupon_data['coupon_final_amount'];
                $UserCoupon->order_id = $order->id;
                $UserCoupon->date_used = now();
                $UserCoupon->theme_id = $theme_id;
                $UserCoupon->save();
            }

            $discount_string = '-' . $coupon_data['coupon_discount_amount'];
            $CURRENCY = Utility::GetValueByName('CURRENCY', $store->theme_id, $store->id);
            $CURRENCY_NAME = Utility::GetValueByName('CURRENCY_NAME', $store->theme_id, $store->id);
            if ($coupon_data['coupon_discount_type'] == 'flat') {
                $discount_string .= $CURRENCY;
            } else {
                $discount_string .= '%';
            }

            $discount_string .= ' ' . __('for all products');
            $order_array['coupon']['code'] = $coupon_data['coupon_code'];
            $order_array['coupon']['discount_string'] = $discount_string;
            $order_array['coupon']['price'] = SetNumber($coupon_data['coupon_final_amount']);
        } elseif (!empty($requests_data['cartlist']['coupon_code'])) {
            $coupon_data = is_object($requests_data['cartlist']['coupon_info']) ? (array) $requests_data['cartlist']['coupon_info'] : $requests_data['cartlist']['coupon_info'];
            $Coupon = Coupon::find($coupon_data['coupon_id']);
            if ($Coupon) {
                // coupon stock decrease end

                // Order Coupon history
                $OrderCouponDetail = new OrderCouponDetail();
                $OrderCouponDetail->order_id = $order->id;
                $OrderCouponDetail->product_order_id = $order->product_order_id;
                $OrderCouponDetail->coupon_id = $coupon_data['coupon_id'];
                $OrderCouponDetail->coupon_name = $coupon_data['coupon_name'];
                $OrderCouponDetail->coupon_code = $coupon_data['coupon_code'];
                $OrderCouponDetail->coupon_discount_type = $coupon_data['coupon_discount_type'];
                $OrderCouponDetail->coupon_discount_number = $coupon_data['coupon_discount_number'];
                $OrderCouponDetail->coupon_discount_amount = $coupon_data['coupon_discount_amount'];
                $OrderCouponDetail->coupon_final_amount = $coupon_data['coupon_final_amount'];
                $OrderCouponDetail->theme_id = $theme_id;
                $OrderCouponDetail->save();

                // Coupon history
                $UserCoupon = new UserCoupon();
                $UserCoupon->user_id = !empty($request->customer_id) ? $request->customer_id : null;
                $UserCoupon->coupon_id = $Coupon->id;
                $UserCoupon->amount = $coupon_data['coupon_discount_amount'];
                $UserCoupon->order_id = $order->id;
                $UserCoupon->date_used = now();
                $UserCoupon->theme_id = $theme_id;
                $UserCoupon->save();
            }


            $discount_string = '-' . $coupon_data['coupon_discount_amount'];
            $CURRENCY = Utility::GetValueByName('CURRENCY');
            $CURRENCY_NAME = Utility::GetValueByName('CURRENCY_NAME');
            if ($coupon_data['coupon_discount_type'] == 'flat') {
                $discount_string .= $CURRENCY;
            } else {
                $discount_string .= '%';
            }

            $discount_string .= ' ' . __('for all products');
            $order_array['coupon']['code'] = $coupon_data['coupon_code'] ?? null;
            $order_array['coupon']['discount_string'] = $discount_string ?? null;
            $order_array['coupon']['price'] = SetNumber($coupon_data['coupon_final_amount'] ?? 0.00);
        }
        // add in Order Coupon Detail table end
        if (isset($requests_data['tax_id_value'])) {
            $taxes = TaxMethod::where('tax_id', $request['tax_id_value'])->where('theme_id', $theme_id)->where('store_id', $store->id)->orderBy('priority', 'asc')->get();
            $other_info = !is_array($requests_data['billing_info']) ? json_decode($requests_data['billing_info']) : $requests_data['billing_info'];
            $country = !empty($other_info->delivery_country) ? $other_info->delivery_country : '';
            $state_id = !empty($other_info->delivery_state) ? $other_info->delivery_state : '';
            $city_id = !empty($other_info->delivery_city) ? $other_info->delivery_city : '';
            foreach ($taxes as $tax) {
                $countryMatch = (!$tax->country_id || $country == $tax->country_id);
                $stateMatch = (!$tax->state_id || $state_id == $tax->state_id);
                $cityMatch = (!$tax->city_id || $city_id == $tax->city_id);

                if ($countryMatch && $stateMatch && $cityMatch) {
                    $OrderTaxDetail = new OrderTaxDetail();
                    $OrderTaxDetail->order_id = $order->id;
                    $OrderTaxDetail->product_order_id = $order->product_order_id;
                    $OrderTaxDetail->tax_id = $tax->id;
                    $OrderTaxDetail->tax_name = $tax->name;
                    $OrderTaxDetail->tax_discount_amount = $tax->tax_rate;
                    $OrderTaxDetail->tax_final_amount = $requests_data['tax_price'];
                    $OrderTaxDetail->theme_id = $theme_id;
                    $OrderTaxDetail->save();
                }
            }
        }

        //activity log
        ActivityLog::order_entry([
            'customer_id' => $order->customer_id,
            'order_id' => $order->product_order_id,
            'order_date' => $order->order_date,
            'products' => $order->product_id,
            'final_price' => $order->final_price,
            'payment_type' => $order->payment_type,
            'theme_id' => $order->theme_id,
            'store_id' => $order->store_id
        ]);
        $other_info = $request->billing_info;

        //Order Mail
        $order_email = $OrderBillingDetail->email ?? (!empty($other_info->email) ? $other_info->email : '');
        $owner = User::find($store->created_by);
        $owner_email = $owner->email;
        $order_id = Crypt::encrypt($order->id);

        if (module_is_active('PreOrder') && \Auth::guard('customers')->user() && isset($requests_data['order_type']) && $requests_data['order_type'] == 'pre_order') {
            \Workdo\PreOrder\app\Models\PreOrderHistory::PreOrderHistory($order, $store, $request, $order_email);
        } else {
            try {
                $dArr = [
                    'order_id' => $order->product_order_id,
                ];

                // Send Email
                $resp = Utility::sendEmailTemplate('Order Created', $order_email, $dArr, $owner, $store, $order_id);
                $resp1 = Utility::sendEmailTemplate('Order Created For Owner', $owner_email, $dArr, $owner, $store, $order_id);
            } catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }
        }

        foreach ($products as $product) {
            $product_data = Product::find($product->product_id);

            if ($product_data) {
                if ($product_data->variant_product == 0) {
                    if ($product_data->track_stock == 1) {
                        OrderNote::order_note_data([
                            'customer_id' => !empty($request->customer_id) ? $request->customer_id : '0',
                            'order_id' => $order->id,
                            'product_name' => !empty($product_data->name) ? $product_data->name : '',
                            'variant_product' => $product_data->variant_product,
                            'product_stock' => !empty($product_data->product_stock) ? $product_data->product_stock : '',
                            'status' => 'Stock Manage',
                            'theme_id' => $order->theme_id,
                            'store_id' => $order->store_id,
                        ]);
                    }
                } else {
                    $variant_data = ProductVariant::find($product->variant_id);
                    $variationOptions = explode(',', $variant_data->variation_option);
                    $option = in_array('manage_stock', $variationOptions);
                    if ($option == true) {
                        OrderNote::order_note_data([
                            'customer_id' => !empty($request->customer_id) ? $request->customer_id : '0',
                            'order_id' => !empty($order->id) ? $order->id : '',
                            'product_name' => !empty($product_data->name) ? $product_data->name : '',
                            'variant_product' => $product_data->variant_product,
                            'product_variant_name' => !empty($variant_data->variant) ? $variant_data->variant : '',
                            'product_stock' => !empty($variant_data->stock) ? $variant_data->stock : '',
                            'status' => 'Stock Manage',
                            'theme_id' => $order->theme_id,
                            'store_id' => $order->store_id,
                        ]);
                    }
                }
            }
        }

        OrderNote::order_note_data([
            'customer_id' => !empty($request->customer_id) ? $request->customer_id : '0',
            'order_id' => $order->id,
            'product_order_id' => $order->product_order_id,
            'delivery_status' => 'Pending',
            'status' => 'Order Created',
            'theme_id' => $order->theme_id,
            'store_id' => $order->store_id
        ]);
        try {
            $msg = __("Hello, Welcome to $store->name .Hi,your order id is $order->product_order_id, Thank you for Shopping We received your purchase request, we'll be in touch shortly!. ");
            $mess = Utility::SendMsgs('Order Created', $OrderBillingDetail->telephone, $msg);
        } catch (\Exception $e) {
            $smtp_error = __('Invalid OAuth access token - Cannot parse access token');
        }
        // add in Order Tax Detail table end
        event(new GetProductStatus($OrderBillingDetail, $order, $owner));
        if (auth('customers')->user() && module_is_active('ProductAffiliate')) {
            Utility::affiliateTransaction($order);
        }
        if (!empty($order) && !empty($OrderBillingDetail)) {

            // $order_array['order_id'] = $order->id;
            // $cart_array = [];
            // $cart_json = json_encode($cart_array);
            // Cookie::queue('cart', $cart_json, 1440);
            $cart = Cookie::get('cart');
            $cart = Cart::where('cookie_session_id', $cart)->delete();
            if (auth('customers')->user()) {
                event(new AddRewardClubPoint($requests_data, $order, $slug));
            }
            return redirect()->route('order.summary', $slug)->with('data', $order->product_order_id);
        } else {
            return $this->error(['message' => 'Somthing went wrong.']);
        }
    }

    public function getWhatsappUrl(Request $request, $slug)
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        $theme_id = $store->theme_id;
        $requests_data = Session::get('request_data');
        $telegram_access_token = \App\Models\Utility::GetValueByName('telegram_access_token', $theme_id, $store->id);
        $telegram_chat_id = \App\Models\Utility::GetValueByName('telegram_chat_id', $theme_id, $store->id);
        $whatsapp_number = \App\Models\Utility::GetValueByName('whatsapp_number', $theme_id, $store->id);
        $cartlist_final_price = 0;
        $final_price = 0;
        $customer_id = $requests_data['customer_id'] ?? 0;

        // cart list api call
        if (!auth('customers')->user()) {
            $response = Cart::cart_list_cookie($request->all(), $store->id);
            $response = json_decode(json_encode($response));
            $cartlist = (array) $response->data;

            if (empty($cartlist['product_list'])) {
                return $this->error(['message' => 'Cart is empty.']);
            }

            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
            $final_price = $cartlist['final_price'] - $cartlist['tax_price'];
            $billing = (isset($requests_data['billing_info']) && is_string($requests_data['billing_info'])) ? json_decode($requests_data['billing_info'], true) : ($requests_data['billing_info'] ?? []);

            $taxes = !empty($cartlist['tax_info']) ? $cartlist['tax_info'] : '';
            $products = $cartlist['product_list'];
        } elseif (!empty($customer_id)) {
            $cart_list['customer_id'] = $customer_id;
            if (is_array($requests_data)) {
                // Merge $requests_data into the request
                $request->merge($requests_data);
            }
            $cartt = new ApiController();
            $cartlist_response = $cartt->cart_list($request, $slug);
            $cartlist = (array) $cartlist_response->getData()->data;

            if (empty($cartlist['product_list'])) {
                return Utility::error(['message' => 'Cart is empty.']);
            }

            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
            $final_price = $cartlist['final_price'] - $cartlist['tax_price'];
            $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
            // $billing = $request->billing_info;
            $taxes = !empty($cartlist['tax_info']) ? $cartlist['tax_info'] : '';
            $products = $cartlist['product_list'];
        } else {
            return Utility::error(['message' => 'User not found.']);
        }

        $prodduct_id_array = [];
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $prodduct_id_array[] = $product->product_id;

                $product_id = $product->product_id;
                $variant_id = $product->variant_id;
                $qtyy = !empty($product->qty) ? $product->qty : 0;

                $Product = Product::where('id', $product_id)->first();
                if (!empty($product_id) && !empty($variant_id) && $product_id != 0 && $variant_id != 0) {
                    $ProductStock = ProductVariant::where('id', $variant_id)->where('product_id', $product_id)->first();
                    if (!empty($ProductStock)) {
                        $remain_stock = $ProductStock->stock - $qtyy;
                        $ProductStock->stock = $remain_stock;
                        $ProductStock->save();
                        $pro_qty[] = $product->qty . ' x ' . $product->name . '-' . $product->variant_name;

                        $lists[] = array(
                            'quantity' => $product->qty,
                            'product_name' => $product->name,
                            'variant_name' => $product->variant_name,
                            'item_total' => $product->final_price * $product->qty,
                        );
                    } else {
                        return Utility::error(['message' => 'Product not found .']);
                    }
                } elseif (!empty($product_id) && $product_id != 0) {
                    if (!empty($Product)) {
                        $remain_stock = $Product->product_stock - $qtyy;
                        $Product->product_stock = $remain_stock;
                        $Product->save();
                        $pro_qty[] = $product->qty . ' x ' . $product->name;

                        $lists[] = array(
                            'quantity' => $product->qty,
                            'product_name' => $product->name,
                            'item_total' => $product->final_price * $product->qty,
                        );

                    } else {
                        return Utility::error(['message' => 'Product not found .']);
                    }
                } else {
                    return Utility::error(['message' => 'Please fill proper product json field .']);
                }
                Cart::where('customer_id', $request->customer_id)->where('product_id', $product_id)->where('variant_id', $variant_id)->where('theme_id', $theme_id)->where('store_id', $store->id)->delete();
            }
        }
        $item_variable = '';
        $qty_total = 0;
        $sub_total = 0;
        $total_tax = 0;
        $tax_price = 0;
        if (!empty($taxes)) {
            foreach ($taxes as $key => $tax) {
                $tax_price += $tax->tax_price;
            }
        }
        $delivery_price = 0;
        // dilivery api call
        $whatsapp_item_variable = \App\Models\Utility::GetValueByName('whatsapp_item_variable', $theme_id, $store->id);
        foreach ($lists as $l) {
            $arrList = [
                'quantity' => $l['quantity'],
                'product_name' => $l['product_name'],
                'item_total' => ($l['item_total']),
            ];

            if (isset($l['variant_name']) && !empty($l['variant_name'])) {
                $arrList['variant_name'] = $l['variant_name'];
            }

            $resp = Utility::replaceVariable($whatsapp_item_variable, $arrList);
            $resp = str_replace('-  ', '', $resp);
            $item_variable .= $resp . PHP_EOL;

            $qty_total = $qty_total + $l['quantity'];
            $sub_total += $l['item_total'] * $l['quantity'];
        }
        $total_price = (floatval($requests_data['cartlist_final_price']));
        $other_info = !is_array($requests_data['billing_info']) ? json_decode($requests_data['billing_info']) : $requests_data['billing_info'];
        $arr = [
            'store_name' => $store->name,
            'order_no' => !empty($request['data']['order_id']) ? $request['data']['order_id'] : '1',
            'customer_name' => !empty($other_info->firstname) ? $other_info->firstname : '-',
            'billing_address' => !empty($other_info->billing_address) ? $other_info->billing_address : '-',
            'billing_country' => !empty($other_info->billing_country) ? $other_info->billing_country : '-',
            'billing_city' => !empty($other_info->billing_city) ? $other_info->billing_city : '-',
            'billing_postalcode' => !empty($other_info->billing_postecode) ? $other_info->billing_postecode : '-',
            'shipping_address' => !empty($other_info->delivery_address) ? $other_info->delivery_address : '-',
            'shipping_country' => !empty($other_info->delivery_country) ? $other_info->delivery_country : '-',
            'shipping_city' => !empty($other_info->delivery_city) ? $other_info->delivery_city : '-',
            'shipping_postalcode' => !empty($other_info->delivery_postcode) ? $other_info->delivery_postcode : '-',
            'item_variable' => $item_variable,
            'qty_total' => $qty_total,
            'sub_total' => ($sub_total),
            'shipping_amount' => (!empty($delivery_price) ? $delivery_price : '0'),
            'total_tax' => ($tax_price),
            'final_total' => $total_price,
        ];
        $settings = getAdminAllSetting();
        $resp = Utility::replaceVariable(($settings['whatsapp_content'] ?? null), $arr) ?? null;

        if ($request['data']['type'] == 'telegram') {
            $msg = $resp;
            // Set your Bot ID and Chat ID.
            $telegrambot = $telegram_access_token;
            $telegramchatid = $telegram_chat_id;

            // Function call with your own text or variable
            $url = 'https://api.telegram.org/bot' . $telegrambot . '/sendMessage';
            $data = array(
                'chat_id' => $telegramchatid,
                'text' => $msg,
            );
            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($data),
                ),
            );
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $url = $url;
        } else {
            $url = 'https://api.whatsapp.com/send?phone=' . $whatsapp_number . '&text=' . urlencode(string: $resp);
        }
        $new_order_id = str_replace('#', '', $request->order_id);
        return $url;
    }

    public function place_order_guest(Request $request, $slug)
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        $theme_id = $store->theme_id;
        $user = User::where('id', $store->created_by)->first();
        if ($user->type == 'admin') {
            $plan = Cache::remember('plan_details_'.$user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
        }

        if ($request->register == 'on') {
            $validator = \Validator::make(
                $request->billing_info,
                [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'billing_address' => 'required',
                    //'billing_postecode' => 'required',
                    'billing_country' => 'required',
                    'billing_state' => 'required',
                    'billing_city' => 'required',
                    'billing_address' => 'required',
                    'email' => [
                        'required',
                        Rule::unique('customers')->where(function ($query) use ($theme_id) {
                            return $query->where('theme_id', $theme_id);
                        })
                    ],

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            // customer
            $insert_array['first_name'] = $request->billing_info['firstname'];
            $insert_array['last_name'] = $request->billing_info['lastname'];
            $insert_array['email'] = $request->billing_info['email'];
            $insert_array['register_type'] = 'email';
            $insert_array['type'] = 'customer';
            $insert_array['mobile'] = !empty($request->billing_info['billing_user_telephone']) ? $request->billing_info['billing_user_telephone'] : '';
            $insert_array['regiester_date'] = date('Y-m-d');
            $insert_array['last_active'] = date('Y-m-d');
            $insert_array['password'] = Hash::make('1234');
            $insert_array['theme_id'] = !empty($store) ? $store->theme_id : '';
            $insert_array['store_id'] = !empty($store) ? $store->id : '';
            $insert_array['created_by'] = !empty($store) ? $store->created_by : '';

            $settings = Setting::where('theme_id', $theme_id)->where('store_id', $store->id)->pluck('value', 'name')->toArray();

            try {
                config(
                    [
                        'mail.driver' => $settings['MAIL_DRIVER'],
                        'mail.host' => $settings['MAIL_HOST'],
                        'mail.port' => $settings['MAIL_PORT'],
                        'mail.encryption' => $settings['MAIL_ENCRYPTION'],
                        'mail.username' => $settings['MAIL_USERNAME'],
                        'mail.password' => $settings['MAIL_PASSWORD'],
                        'mail.from.address' => $settings['MAIL_FROM_ADDRESS'],
                        'mail.from.name' => $settings['MAIL_FROM_NAME'],
                    ]
                );
                $email = $request->billing_info['email'];

                $status = Password::sendResetLink([
                    'email' => $email,
                ]);
            } catch (\Throwable $th) {
            }

            $customer = Customer::create($insert_array);
            //activity log
            ActivityLog::create([
                'customer_id' => $customer->id,
                'log_type' => 'register',
                'store_id' => !empty($store) ? $store->id : '',
                'theme_id' => $theme_id,
            ]);

            // UserAdditionalDetail::create([
            //     'customer_id' => $customer->id,
            //     'theme_id' => !empty($store) ? $store->theme_id : '',
            // ]);
            $validator = \Validator::make(
                $request->billing_info,
                [
                    'firstname' => 'required',
                    'lastname' => 'required',

                    'email' => [
                        'required',
                        Rule::unique('customers')->where(function ($query) use ($theme_id) {
                            return $query->where('theme_id', $theme_id);
                        })
                    ],

                ]
            );

            $request->merge([
                'store_id' => $store->id,
                'slug' => $slug,
                'customer_id' => $customer->id,
                'default_address' => 1,
                'first_name' => $request->billing_info['firstname'],
                'address' => $request->billing_info['billing_address'],
                'country' => $request->billing_info['billing_country'],
                'state' => $request->billing_info['billing_state'],
                'city' => $request->billing_info['billing_city'],
                'postcode' => $request->billing_info['billing_postecode'],
                'title' => strtolower($request->billing_info['firstname']),

            ]);

            $api = new ApiController();
            $data = $api->add_address($request, $slug);
            $response = $data->getData();



            auth('customers')->login($customer);
        } else {
            $validator = \Validator::make(
                $request->billing_info,
                [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'billing_address' => 'required',
                    //'billing_postecode' => 'required',
                    'billing_country' => 'required',
                    'billing_state' => 'required',
                    'billing_city' => 'required',
                    'billing_address' => 'required',
                    'email' => [
                        'required',
                        'email'
                        // Rule::unique('customers')->where(function ($query) use ($theme_id) {
                        //     return $query->where('theme_id', $theme_id);
                        // })
                    ],

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

           // $customer = Customer::where('email', $request->billing_info['email'])->where('regiester_date', null)->get();
            $customer = Customer::where('email', $request->billing_info['email'])->first();
            if (!$customer) {
                $insert_array['first_name'] = $request->billing_info['firstname'];
                $insert_array['last_name'] = $request->billing_info['lastname'];
                $insert_array['email'] = $request->billing_info['email'];
                $insert_array['register_type'] = 'email';
                $insert_array['type'] = 'customer';
                $insert_array['mobile'] = !empty($request->billing_info['billing_user_telephone']) ? $request->billing_info['billing_user_telephone'] : '';
                $insert_array['last_active'] = date('Y-m-d');
                $insert_array['theme_id'] = !empty($store->theme_id) ? $store->theme_id : '';
                $insert_array['store_id'] = !empty($store->id) ? $store->id : null;
                $insert_array['created_by'] = !empty($store->created_by) ? $store->created_by : null;

                $customer = Customer::create($insert_array);

                $request->merge([
                    'store_id' => $store->id,
                    'slug' => $slug,
                    'customer_id' => $customer->id,
                    'default_address' => 1,
                    'first_name' => $request->billing_info['firstname'],
                    'address' => $request->billing_info['billing_address'],
                    'country' => $request->billing_info['billing_country'],
                    'state' => $request->billing_info['billing_state'],
                    'city' => $request->billing_info['billing_city'],
                    'postcode' => $request->billing_info['billing_postecode'],
                    'title' => strtolower($request->billing_info['firstname']),

                ]);

                $api = new ApiController();
                $data = $api->add_address($request, $slug);
                $response = $data->getData();
            } else {
                $customer = Customer::where('email', $request->billing_info['email'])->first();
                $customer->last_active = date('Y-m-d');
                $customer->save();
            }
        }

        if (isset($request->billing_info) && is_string($request->billing_info)) {
            $param['billing_info'] = (array) json_decode($request->billing_info);
            unset($request->billing_info);
        }

        if ($request->delivery_and_billing == 'on' || $request->delivery_and_billing == 'true' || $request->delivery_and_billing == true) {
            $param['billing_info']['firstname'] = $request->billing_info['firstname'];
            $param['billing_info']['lastname'] = $request->billing_info['lastname'];
            $param['billing_info']['email'] = $request->billing_info['email'];
            $param['billing_info']['billing_user_telephone'] = $request->billing_info['billing_user_telephone'];

            $param['billing_info']['billing_address'] = $request->billing_info['billing_address'];
            $param['billing_info']['billing_country'] = $request->billing_info['billing_country'];
            $param['billing_info']['billing_state'] = $request->billing_info['billing_state'];
            $param['billing_info']['billing_city'] = $request->billing_info['billing_city'];
            $param['billing_info']['billing_postecode'] = $request->billing_info['billing_postecode'];

            $param['billing_info']['delivery_address'] = $request->billing_info['billing_address'];
            $param['billing_info']['delivery_country'] = $request->billing_info['billing_country'];
            $param['billing_info']['delivery_state'] = $request->billing_info['billing_state'];
            $param['billing_info']['delivery_city'] = $request->billing_info['billing_city'];
            $param['billing_info']['delivery_postcode'] = $request->billing_info['billing_postecode'];
        }

        $cart = Cookie::get('cart');
        $cart_array = Cart::where('cookie_session_id',$cart)->get();
        $new_array = [];

        // Product array
        $i = 0;
        foreach ($cart_array as $key => $value) {
            $new_array['product'][$i]['product_id'] = $value->product_id;
            $new_array['product'][$i]['variant_id'] = $value->variant_id;
            $new_array['product'][$i]['qty'] = $value->qty;
            $i++;
        }
        $new_array['tax_info'] = [];

        // TAX array
        $param['theme_id'] = $theme_id;
        $param['slug'] = $slug;
        $param['store_id'] = $store->id;

        $request->merge($param);
        $ApiController = new ApiController();


        if (!empty($request->coupon_code) && !isset($request['coupon_info'])) {
            $new_array['coupon_info'] = [];
            $apply_coupon_data = $ApiController->apply_coupon($request, $slug);
            $apply_coupon = $apply_coupon_data->getData();
            if ($apply_coupon->status == 1) {
                $new_array['coupon_info']['coupon_id'] = $apply_coupon->data->id;
                $new_array['coupon_info']['coupon_name'] = $apply_coupon->data->name;
                $new_array['coupon_info']['coupon_code'] = $apply_coupon->data->code;
                $new_array['coupon_info']['coupon_discount_type'] = $apply_coupon->data->coupon_discount_type;
                $new_array['coupon_info']['coupon_discount_number'] = $apply_coupon->data->amount;
                $new_array['coupon_info']['coupon_discount_amount'] = $apply_coupon->data->coupon_discount_amount;
                $new_array['coupon_info']['coupon_final_amount'] = $apply_coupon->data->final_price;
            }
            if (!empty($request->coupon_code)) {
                $cart_price = [
                    'sub_total' => $apply_coupon->data->final_price
                ];
                $request->request->add($cart_price);
            }
        }

        // coupon array
        if ($request->register == 'on') {
            $new_array['customer_id'] = $customer->id ?? $user->id;
        } else {
            $new_array['customer_id'] = $customer ? $customer->id : 0;
        }

        // $new_array['user_id'] = 0;
        $new_array['shipping_id'] = $request->delivery_id;
        $new_array['slug'] = $slug;
        $new_array['store_id'] = $store->id;
        $request->merge($new_array);

        if ($request->payment_type == 'stripe' || $request->payment_type == 'paystack' || $request->payment_type == 'skrill' || $request->payment_type == 'mercado' || $request->payment_type ==     'paymentwall' || $request->payment_type == 'Razorpay' || $request->payment_type == 'paypal' || $request->payment_type == 'flutterwave' || $request->payment_type == 'paytm' || $request->payment_type == 'mollie' || $request->payment_type == 'coingate' || $request->payment_type == 'toyyibpay' || $request->payment_type == 'Sspay' || $request->payment_type == 'Paytabs' || $request->payment_type == 'iyzipay' || $request->payment_type == 'payfast' || $request->payment_type == 'benefit' || $request->payment_type == 'cashfree' || $request->payment_type == 'aamarpay' || $request->payment_type == 'telegram' || $request->payment_type == 'whatsapp' || $request->payment_type == 'paytr' || $request->payment_type == 'yookassa' || $request->payment_type == 'Xendit' || $request->payment_type == 'midtrans' || $request->payment_type == 'cod' || $request->payment_type == 'bank_transfer' || $request->payment_type == 'Nepalste'  || $request->payment_type == 'PayHere' || $request->payment_type == 'khalti' || $request->payment_type == 'AuthorizeNet' || $request->payment_type == 'Tap' || $request->payment_type == 'PhonePe' || $request->payment_type == 'Paddle' || $request->payment_type == 'Paiementpro' || $request->payment_type == 'FedPay' || $request->payment_type == 'CinetPay' || $request->payment_type == 'SenagePay' ||$request->payment_type == 'CyberSource' ||$request->payment_type == 'Ozow'  || $request->payment_type == 'MyFatoorah' || $request->payment_type == 'easebuzz' || $request->payment_type == 'NMI' || $request->payment_type == 'payu' || $request->payment_type == 'sofort' || $request->payment_type == 'esewa' || $request->payment_type == 'Paynow' || $request->payment_type == 'DPO' || $request->payment_type == 'Braintree' || $request->payment_type == 'PowerTranz' || $request->payment_type == 'SSLCommerz') {
            $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;

            if (empty($billing['firstname'])) {
                return redirect()->back()->with('error', __('Billing first name not found.'));
            }
            if (empty($billing['lastname'])) {
                return redirect()->back()->with('error', __('Billing last name not found.'));
            }
            if (empty($billing['email'])) {
                return redirect()->back()->with('error', __('Billing email not found.'));
            }
            if (empty($billing['billing_user_telephone'])) {
                return redirect()->back()->with('error', __('Billing telephone not found.'));
            }
            if (empty($billing['billing_address'])) {
                return redirect()->back()->with('error', __('Billing address not found.'));
            }
            // if (empty($billing['billing_postecode'])) {
            //     return redirect()->back()->with('error', __('Billing postecode not found.'));
            // }
            if (empty($billing['billing_country'])) {
                return redirect()->back()->with('error', __('Billing country not found.'));
            }
            if (empty($billing['billing_state'])) {
                return redirect()->back()->with('error', __('Billing state not found.'));
            }
            if (empty($billing['billing_city'])) {
                return redirect()->back()->with('error', __('Billing city not found.'));
            }
            if (empty($billing['delivery_address'])) {
                return redirect()->back()->with('error', __('Delivery address not found.'));
            }
            // if (empty($billing['delivery_postcode'])) {
            //     return redirect()->back()->with('error', __('Delivery postcode not found.'));
            // }
            if (empty($billing['delivery_country'])) {
                return redirect()->back()->with('error', __('Delivery country not found.'));
            }
            if (empty($billing['delivery_state'])) {
                return redirect()->back()->with('error', __('Delivery state not found.'));
            }
            if (empty($billing['delivery_city'])) {
                return redirect()->back()->with('error', __('Delivery city not found.'));
            }

            $cartlist_final_price = 0;
            $final_price = 0;
            if (!auth('customers')->user()) {
                $response = Cart::cart_list_cookie($request->all(), $store->id);
                $response = json_decode(json_encode($response));
                $cartlist = (array) $response->data;

                if (empty($cartlist['product_list'])) {
                    return redirect()->back()->with('error', 'Cart is empty.');
                }

                if (module_is_active('CartQuantityControl')) {
                    $qtyControlResponse = \Workdo\CartQuantityControl\app\Models\CartQuantityControl::minQtyCheckoutValidation($cartlist);
                    if (isset($qtyControlResponse) && !empty($qtyControlResponse)) {

                        $out_of_stock_Message = isset($qtyControlResponse['out_of_stock']) ? implode('<br>', $qtyControlResponse['out_of_stock']) : '';
                        $min_quantity_Message = isset($qtyControlResponse['min_quantity']) ? implode(' , ', $qtyControlResponse['min_quantity']) : '';
                        $max_quantity_Message = isset($qtyControlResponse['max_quantity']) ? implode(' , ', $qtyControlResponse['max_quantity']) : '';

                        if (!empty($out_of_stock_Message) && !empty($min_quantity_Message)) {
                            return redirect()->back()->with('error', 'The minimum allowed order quantity for <b>' . $min_quantity_Message . '</b> Please add more quantity. <br>' . $out_of_stock_Message);
                        } elseif (!empty($out_of_stock_Message)) {
                            return redirect()->back()->with('error', $out_of_stock_Message);
                        } elseif (!empty($min_quantity_Message)) {
                            return redirect()->back()->with('error', 'The minimum allowed order quantity for <b>' . $min_quantity_Message . '</b> Please add more quantity.');
                        } elseif (!empty($max_quantity_Message)) {
                            return redirect()->back()->with('error', 'The maximum allowed order quantity for <b>' . $max_quantity_Message . '</b> Please remove some quantity.');
                        }
                    }
                }
                $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
                $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
                $final_price = $response->data->total_final_price;
                $tax_price = !empty($cartlist['total_tax_price']) ? $cartlist['total_tax_price'] : '';
                $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : ($request->billing_info ?? []);
                $products = $cartlist['product_list'];
            } elseif (!empty($request->customer_id)) {
                $cart_list['customer_id'] = $customer->id;
                $request->request->add($cart_list);

                if ($request->register == 'on') {
                    Cart::cookie_to_cart($customer->id, $store->id, $theme_id);
                }

                $cart_lists = new ApiController();
                $cartlist_response = $cart_lists->cart_list($request, $slug);
                $cartlist = (array) $cartlist_response->getData()->data;

                if (empty($cartlist['product_list'])) {
                    return redirect()->back()->with('error', 'Cart is empty.');
                }
                $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
                $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
                $final_price = $cartlist['total_final_price'];
                $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
                $tax_price = $cartlist['total_tax_price'] ?? '';
                $products = $cartlist['product_list'];
            } else {
                return redirect()->back()->with('error', 'User not found.');
            }

            $coupon_price = 0;
            // coupon api call
            if (!empty($request['coupon_code'])) {

                if (isset($request['coupon_info']) && $request['coupon_info']) {
                    $cartlist_final_price = $coupon_price = $request['coupon_info']['coupon_final_amount'] ?? 0;   //coupon_discount_amount
                } else {
                    $coupon_data = $request->all();
                    $apply_coupon = [
                        'coupon_code' => $coupon_data['coupon_code'],
                        'sub_total' => $cartlist_final_price

                    ];
                    $request->request->add($apply_coupon);
                    $coupon_apply = new ApiController();
                    $apply_coupon_response = $coupon_apply->apply_coupon($request, $slug);
                    $apply_coupon = (array) $apply_coupon_response->getData()->data;

                    $order_array['coupon']['message'] = $apply_coupon['message'];
                    $order_array['coupon']['status'] = false;
                    if (!empty($apply_coupon['final_price'])) {
                        $cartlist_final_price = $apply_coupon['final_price'];
                        $coupon_price = $apply_coupon['amount'];
                        $order_array['coupon']['status'] = true;
                    }
                }
            }

            // dilivery api call
            $delivery_price = 0;
            if ($plan->shipping_method == 'on') {
                if (!empty($request->method_id)) {
                    $del_charge = new CartController();
                    $delivery_charge = $del_charge->get_shipping_method($request, $slug);
                    $content = $delivery_charge->getContent();

                    $data = json_decode($content, true);

                    $delivery_price = $data['total_final_price'];

                    $tax_price = $data['final_tax_price'];

                }
            } else {
                if (!empty($tax_price)) {
                    $tax_price = $tax_price;
                } else {
                    $tax_price = 0;
                }
            }
            $new_array['shipping_final_price'] = $delivery_price;
            $new_array['tax_price'] = $tax_price;
            $request->merge($new_array);
            if (!empty($prodduct_id_array)) {
                $prodduct_id_array = $prodduct_id_array = array_unique($prodduct_id_array);
                $prodduct_id_array = implode(',', $prodduct_id_array);
            } else {
                $prodduct_id_array = '';
            }

            $new_array['cartlist_final_price'] = $cartlist_final_price + $delivery_price + $tax_price;
            //$new_array['cartlist_final_price'] = $cartlist_final_price;
            $request->merge($new_array);

            $product_reward_point = 1;

            $new_array['billing_info'] = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
            $request->merge($new_array);
            $paymentMethod = $request->payment_type;
            $response = $this->paymentService->process($request, $paymentMethod, $slug, $cartlist);

            if ($request->payment_type == 'paystack' || $request->payment_type == 'Razorpay' || $request->payment_type == 'paymentwall' || $request->payment_type == 'flutterwave' || $request->payment_type == 'paytm' || $request->payment_type == 'telegram' || $request->payment_type == 'paytr' || $request->payment_type == 'midtrans' || $request->payment_type == 'whatsapp' || $request->payment_type == 'PayHere' || $request->payment_type == 'khalti' || $request->payment_type == 'AuthorizeNet') {
                if ($response instanceof \Illuminate\Http\RedirectResponse) {
                    return new RedirectResponse($response->getTargetUrl());
                }
                $viewHtml = $response->render();
                return $viewHtml;
            }elseif ($request->payment_type == 'skrill'  || $request->payment_type == 'Sspay'   || $request->payment_type == 'toyyibpay' || $request->payment_type == 'Paytabs' || $request->payment_type == 'cod' || $request->payment_type == 'bank_transfer' || $request->payment_type == 'Nepalste' || $request->payment_type == 'Tap' || $request->payment_type == 'PhonePe' || $request->payment_type == 'Paddle' || $request->payment_type == 'Paiementpro' || $request->payment_type == 'FedPay' || $request->payment_type == 'CinetPay' || $request->payment_type == 'SenagePay' ||$request->payment_type == 'Ozow'  || $request->payment_type == 'easebuzz' || $request->payment_type == 'esewa' || $request->payment_type == 'Paynow' || $request->payment_type == 'DPO' || $request->payment_type == 'MyFatoorah'){
                if( $request->payment_type == 'Paytabs')
                {
                    return redirect($response->getTargetUrl());
                }
                return new RedirectResponse($response->getTargetUrl());
            } elseif ($request->payment_type == 'coingate') {
                return new RedirectResponse($response->payment_url);
            } elseif ($request->payment_type == 'payfast' || $request->payment_type == 'CyberSource' || $request->payment_type == 'NMI' || $request->payment_type == 'payu' || $request->payment_type == 'Braintree' || $request->payment_type == 'PowerTranz' || $request->payment_type == 'SSLCommerz') {
                return $response;
            } else {
                return new RedirectResponse($response);
            }

        } else {
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    public function whatsapp(Request $request, $slug)
    {
        $requests_data = (Session::get('request_data'));

        if ($requests_data == null) {
            $requests_data = $request->all();
        }
        $slug = !empty($requests_data['slug']) ? $requests_data['slug'] : '';
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });

        Session::forget('request_data');
        $customer_id = $requests_data['customer_id'] ?? '';

        if (!empty($requests_data['method_id'])) {

            $request['method_id'] = $requests_data['method_id'];
        }
        $user = Cache::remember('admin_details', 3600, function () {
            return User::where('type','admin')->first();
        });
        if ($user->type == 'admin') {
            $plan = Cache::remember('plan_details_'.$user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
        }
        $theme_id = !empty($request->theme_id) ? $request->theme_id : APP_THEME();
        if (!auth('customers')->user()) {
            if ($request->coupon_code != null) {
                $coupon = Coupon::where('id', $request->coupon_info['coupon_id'])->where('store_id', $store->id)->where('theme_id', $theme_id)->first();
                $coupon_email = $coupon->PerUsesCouponCount();
                $i = 0;
                foreach ($coupon_email as $email) {
                    if ($email == $request->billing_info['email']) {
                        $i++;
                    }
                }

                if (!empty($coupon->coupon_limit_user)) {
                    if ($i >= $coupon->coupon_limit_user) {
                        return $this->error(['message' => 'This coupon has expired.']);
                    }
                }
            }
        }
        if (!auth('customers')->user()) {
            $rules = [
                'billing_info' => 'required',
                'payment_type' => 'required',
                //'delivery_id' => 'required',
            ];
        } else {
            $rules = [
                'customer_id' => 'required',
                'billing_info' => 'required',
                'payment_type' => 'required',
                //'delivery_id' => 'required',
            ];
        }

        $validator = \Validator::make($requests_data, $rules);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            Utility::error([
                'message' => $messages->first()
            ]);
        }

        $cartlist_final_price = 0;
        $final_price = 0;
        $tax_price = 0;
        // cart list api
        if (!auth('customers')->user()) {

            $response = Cart::cart_list_cookie($requests_data, $store->id);

            $response = json_decode(json_encode($response));
            $cartlist = (array) $response->data;
            if (empty($cartlist['product_list'])) {
                return $this->error(['message' => 'Cart is empty.']);
            }
            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
            $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
            $final_price = $response->data->total_final_price;
            $tax_price = !empty($requests_data['tax_price']) ? $requests_data['tax_price'] : '';
            $billing = $requests_data['billing_info'];

            $products = $cartlist['product_list'];
        } elseif (!empty($customer_id)) {
            $cart_list['customer_id'] = $customer_id;
            if (is_array($requests_data)) {
                // Merge $requests_data into the request
                $request->merge($requests_data);
            }
            $cartt = new ApiController();
            $cartlist_response = $cartt->cart_list($request, $slug);
            $cartlist = (array) $cartlist_response->getData()->data;
            if (empty($cartlist['product_list'])) {
                return $this->error(['message' => 'Cart is empty.']);
            }

            $cartlist_final_price = !empty($cartlist['final_price']) ? $cartlist['final_price'] : 0;
            $final_sub_total_price = !empty($cartlist['total_sub_price']) ? $cartlist['total_sub_price'] : 0;
            $final_price = $cartlist['total_final_price'];
            $tax_price = !empty($requests_data['tax_price']) ? $requests_data['tax_price'] : '';
            $billing = is_string($request->billing_info) ? (array) json_decode($request->billing_info) : $request->billing_info;
            $products = $cartlist['product_list'];
        } else {
            return Utility::error(['message' => 'User not found.']);
        }

        $coupon_price = 0;
        // coupon api call
        if (!empty($requests_data['coupon_info'])) {
            $coupon_data = $requests_data['coupon_info'];
            $apply_coupon = [
                'coupon_code' => $coupon_data['coupon_code'],
                'sub_total' => $cartlist_final_price,
                'theme_id' => $requests_data['theme_id'],
                'slug' => $requests_data['slug']

            ];
            $request->merge($apply_coupon);
            $couponss = new ApiController();
            $apply_coupon_response = $couponss->apply_coupon($request, $slug);
            $apply_coupon = (array) $apply_coupon_response->getData()->data;

            $order_array['coupon']['message'] = $apply_coupon['message'];
            $order_array['coupon']['status'] = false;
            if (!empty($apply_coupon['final_price'])) {
                $cartlist_final_price = $apply_coupon['final_price'];
                $coupon_price = $apply_coupon['amount'];
                $order_array['coupon']['status'] = true;
            }
        } elseif (!empty($requests_data['cartlist']['coupon_info'])) {
            $coupon_data = is_object($requests_data['cartlist']['coupon_info']) ? (array) $requests_data['cartlist']['coupon_info'] : $requests_data['cartlist']['coupon_info'];

            $apply_coupon = [
                'coupon_code' => $coupon_data['coupon_code'],
                'sub_total' => $cartlist_final_price,
                'theme_id' => $requests_data['theme_id'],
                'slug' => $requests_data['slug']

            ];
            $request->merge($apply_coupon);
            $couponss = new ApiController();
            $apply_coupon_response = $couponss->apply_coupon($request, $slug);
            $apply_coupon = (array) $apply_coupon_response->getData()->data;
            $order_array['coupon']['message'] = $apply_coupon['message'];
            $order_array['coupon']['status'] = false;
            if (!empty($apply_coupon['final_price'])) {
                $cartlist_final_price = $apply_coupon['final_price'];
                $coupon_price = $apply_coupon['amount'];
                $order_array['coupon']['status'] = true;
            }
        }

        $delivery_price = 0;
        if ($plan->shipping_method == 'on') {
            if (!empty($request->method_id)) {
                $del_charge = new CartController();
                $delivery_charge = $del_charge->get_shipping_method($request, $slug);
                $content = $delivery_charge->getContent();
                $data = json_decode($content, true);
                $delivery_price = $data['total_final_price'];
                $tax_price = $requests_data['tax_price'] ?? 0;
            } else {
                return $this->error(['message' => 'Shipping Method not found']);
            }
        } else {
            if (!empty($tax_price)) {
                $tax_price = $tax_price;
            } else {
                $tax_price = 0;
            }
        }

        $settings = Setting::where('theme_id', $theme_id)->where('store_id', $store->id)->pluck('value', 'name')->toArray();

        // Order stock decrease start
        $prodduct_id_array = [];
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $prodduct_id_array[] = $product->product_id;

                $product_id = $product->product_id;
                $variant_id = $product->variant_id;
                $qtyy = !empty($product->qty) ? $product->qty : 0;

                $Product = Product::where('id', $product_id)->first();
                $datas = Product::find($product_id);
                if ($settings['stock_management'] ?? '' == 'on') {
                    if (!empty($product_id) && !empty($variant_id) && $product_id != 0 && $variant_id != 0) {
                        $ProductStock = ProductVariant::where('id', $variant_id)->where('product_id', $product_id)->first();
                        $variationOptions = explode(',', $ProductStock->variation_option);
                        $option = in_array('manage_stock', $variationOptions);
                        if (!empty($ProductStock)) {
                            if ($option == true) {
                                $remain_stock = $ProductStock->stock - $qtyy;
                                $ProductStock->stock = $remain_stock;
                                $ProductStock->save();

                                if ($ProductStock->stock <= $ProductStock->low_stock_threshold) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_low_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_low_stock_threshold($product, $ProductStock, $theme_id, $settings);
                                        }

                                    }
                                }
                                if ($ProductStock->stock <= $settings['out_of_stock_threshold']) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_out_of_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_out_of_stock($product, $ProductStock, $theme_id, $settings);
                                        }
                                    }
                                }
                            } else {
                                $remain_stock = $datas->product_stock - $qtyy;
                                $datas->product_stock = $remain_stock;
                                $datas->save();
                                if ($datas->product_stock <= $datas->low_stock_threshold) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_low_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_low_stock_threshold($product, $datas, $theme_id, $settings);
                                        }

                                    }
                                }
                                if ($datas->product_stock <= $settings['out_of_stock_threshold']) {
                                    if (!empty(json_decode($settings['notification'])) && in_array("enable_out_of_stock", json_decode($settings['notification']))) {
                                        if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                            Utility::variant_out_of_stock($product, $datas, $theme_id, $settings);
                                        }
                                    }
                                }
                                if ($datas->product_stock <= $settings['out_of_stock_threshold'] && $datas->stock_order_status == 'notify_customer') {
                                    //Stock Mail
                                    $order_email = $billing['email'];
                                    $owner = User::find($store->created_by);
                                    $ProductId = '';

                                    try {
                                        $dArr = [
                                            'item_variable' => $Product->id,
                                            'product_name' => $Product->name,
                                            'customer_name' => $billing['firstname'],
                                        ];

                                        // Send Email
                                        $resp = Utility::sendEmailTemplate('Stock Status', $order_email, $dArr, $owner, $store, $ProductId);
                                    } catch (\Exception $e) {
                                        $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                                    }
                                    try {
                                        $mobile_no = $request['billing_info']['billing_user_telephone'];
                                        $customer_name = $request['billing_info']['firstname'];
                                        $msg = __("Dear,$customer_name .Hi,We are excited to inform you that the product you have been waiting for is now back in stock.Product Name: :$Product->name. ");
                                        $resp = Utility::SendMsgs('Stock Status', $mobile_no, $msg);
                                    } catch (\Exception $e) {
                                        $smtp_error = __('Invalid OAuth access token - Cannot parse access token');
                                    }
                                }
                            }
                        } else {
                            return $this->error(['message' => 'Product not found .']);
                        }
                    } elseif (!empty($product_id) && $product_id != 0) {

                        if (!empty($Product)) {
                            $remain_stock = $Product->product_stock - $qtyy;
                            $Product->product_stock = $remain_stock;
                            $Product->save();
                            if ($Product->product_stock <= $Product->low_stock_threshold) {
                                if (!empty(json_decode($settings['notification'])) && in_array("enable_low_stock", json_decode($settings['notification']))) {
                                    if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                        Utility::low_stock_threshold($Product, $theme_id, $settings);
                                    }
                                }
                            }

                            if ($Product->product_stock <= $settings['out_of_stock_threshold']) {
                                if (!empty(json_decode($settings['notification'])) && in_array("enable_out_of_stock", json_decode($settings['notification']))) {
                                    if (isset($settings['twilio_setting_enabled']) && $settings['twilio_setting_enabled'] == "on") {
                                        Utility::out_of_stock($Product, $theme_id, $settings);
                                    }
                                }
                            }

                            if ($Product->product_stock <= $settings['out_of_stock_threshold'] && $Product->stock_order_status == 'notify_customer') {
                                //Stock Mail
                                $order_email = $billing['email'];
                                $owner = User::find($store->created_by);
                                $ProductId = '';

                                try {
                                    $dArr = [
                                        'item_variable' => $Product->id,
                                        'product_name' => $Product->name,
                                        'customer_name' => $billing['firstname'],
                                    ];

                                    // Send Email
                                    $resp = Utility::sendEmailTemplate('Stock Status', $order_email, $dArr, $owner, $store, $ProductId);
                                } catch (\Exception $e) {
                                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                                }
                                try {
                                    $mobile_no = $request['billing_info']['billing_user_telephone'];
                                    $customer_name = $request['billing_info']['firstname'];
                                    $msg = __("Dear,$customer_name .Hi,We are excited to inform you that the product you have been waiting for is now back in stock.Product Name: :$Product->name. ");
                                    $resp = Utility::SendMsgs('Stock Status', $mobile_no, $msg);
                                } catch (\Exception $e) {
                                    $smtp_error = __('Invalid OAuth access token - Cannot parse access token');
                                }
                            }

                        } else {
                            return $this->error(['message' => 'Product not found .']);
                        }
                    } else {
                        return $this->error(['message' => 'Please fill proper product json field .']);
                    }
                }
                // remove from cart
                Cart::where('customer_id', $request->customer_id)->where('product_id', $product_id)->where('variant_id', $variant_id)->where('theme_id', $theme_id)->where('store_id', $store->id)->delete();
            }
        }
        // Order stock decrease end
        if (!empty($prodduct_id_array)) {
            $prodduct_id_array = $prodduct_id_array = array_unique($prodduct_id_array);
            $prodduct_id_array = implode(',', $prodduct_id_array);
        } else {
            $prodduct_id_array = '';
        }

        $product_reward_point = 1;

        $product_order_id = '0' . date('YmdHis');
        $is_guest = 1;
        if (auth('customers')->check()) {
            $product_order_id = $request->customer_id . date('YmdHis');
            $is_guest = 0;
        }

        // add in  Order table  start
        $order = new Order();
        $order->product_order_id = $product_order_id;
        $order->order_date = date('Y-m-d H:i:s');
        $order->customer_id = !empty($request->customer_id) ? $request->customer_id : 0;
        $order->is_guest = $is_guest;
        $order->product_id = $prodduct_id_array;
        $order->product_json = json_encode($products);
        $order->product_price = $final_sub_total_price;
        $order->coupon_price = $coupon_price;
        $order->delivery_price = $delivery_price;
        $order->tax_price = $tax_price;
        if (!auth('customers')->user()) {
            if ($plan->shipping_method == "on") {
                $order->final_price = $final_price + $delivery_price;
            } else {
                $order->final_price = $final_price;
            }

        } else {
            if (module_is_active('RewardClubPoint') && isset($requests_data['club_point_is_active']) && $requests_data['club_point_is_active'] == 'on') {
                $customerDetail = Customer::find($customer_id);
                $rewardSetting = getAdminAllSetting($store->created_by, $store->id, $store->theme_id) ?? null;
                $saveRewardPrice = ($customerDetail->total_club_point * $rewardSetting['reward_point_price'] ?? 0) / ($rewardSetting['reward_points'] ?? 0);
                if ($plan->shipping_method == "on") {
                    $order->final_price = $final_price + $delivery_price - $saveRewardPrice;
                } else {
                    $order->final_price = $final_price - $saveRewardPrice;
                }
            } else {
                if ($plan->shipping_method == "on") {
                    $order->final_price = $final_price + $delivery_price;
                } else {
                    $order->final_price = $final_price;
                }
            }
        }
        $order->payment_comment = !empty($requests_data['payment_comment']) ? $requests_data['payment_comment'] : '';
        $order->payment_type = $requests_data['payment_type'];
        $order->payment_status = 'Paid';
        $order->delivery_id = $requests_data['method_id'] ?? 0;
        $order->delivery_comment = !empty($requests_data['delivery_comment']) ? $requests_data['delivery_comment'] : '';
        if (module_is_active('PreOrder') && \Auth::guard('customers')->user() && isset($requests_data['order_type']) && $requests_data['order_type'] == 'pre_order') {
            $order->delivered_status = 8;
        }else{
            $order->delivered_status = 0;
        }
        $order->reward_points = SetNumber($product_reward_point);
        $order->additional_note = $request->additional_note;
        $order->theme_id = $store->theme_id;
        $order->store_id = $store->id;
        $order->save();
        // add in  Order table end
        if (module_is_active('CheckoutAttachment')) {
            \Workdo\CheckoutAttachment\app\Models\CheckoutAttachment::CheckoutAttachmentOrderStore($order, $slug);
        }
        // Utility::paymentWebhook($order);

        $billing_city_id = 0;
        if (!empty($billing['billing_city'])) {
            $cityy = City::where('name', $billing['billing_city'])->first();
            if (!empty($cityy)) {
                $billing_city_id = $cityy->id;
            } else {
                $new_billing_city = new City();
                $new_billing_city->name = $billing['billing_city'];
                $new_billing_city->state_id = $billing['billing_state'];
                $new_billing_city->country_id = $billing['billing_country'];
                $new_billing_city->save();
                $billing_city_id = $new_billing_city->id;
            }
        }

        $delivery_city_id = 0;
        if (!empty($billing['delivery_city'])) {
            $d_cityy = City::where('name', $billing['delivery_city'])->first();
            if (!empty($d_cityy)) {
                $delivery_city_id = $d_cityy->id;
            } else {
                $new_delivery_city = new City();
                $new_delivery_city->name = $billing['delivery_city'];
                $new_delivery_city->state_id = $billing['delivery_state'];
                $new_delivery_city->country_id = $billing['delivery_country'];
                $new_delivery_city->save();
                $delivery_city_id = $new_delivery_city->id;
            }
        }
        event(new AddAdditionalFields($order, $request->all(), $store));
        $OrderBillingDetail = new OrderBillingDetail();
        $OrderBillingDetail->order_id = $order->id;
        $OrderBillingDetail->product_order_id = $order->product_order_id;
        $OrderBillingDetail->first_name = !empty($billing['firstname']) ? $billing['firstname'] : '';
        $OrderBillingDetail->last_name = !empty($billing['lastname']) ? $billing['lastname'] : '';
        $OrderBillingDetail->email = !empty($billing['email']) ? $billing['email'] : '';
        $OrderBillingDetail->telephone = !empty($billing['billing_user_telephone']) ? $billing['billing_user_telephone'] : '';
        $OrderBillingDetail->address = !empty($billing['billing_address']) ? $billing['billing_address'] : '';
        $OrderBillingDetail->postcode = !empty($billing['billing_postecode']) ? $billing['billing_postecode'] : '';
        $OrderBillingDetail->country = !empty($billing['billing_country']) ? $billing['billing_country'] : '';
        $OrderBillingDetail->state = !empty($billing['billing_state']) ? $billing['billing_state'] : '';
        $OrderBillingDetail->city = $billing_city_id;
        $OrderBillingDetail->theme_id = $theme_id;
        $OrderBillingDetail->delivery_address = !empty($billing['delivery_address']) ? $billing['delivery_address'] : '';
        $OrderBillingDetail->delivery_city = $delivery_city_id;
        $OrderBillingDetail->delivery_postcode = !empty($billing['delivery_postcode']) ? $billing['delivery_postcode'] : '';
        $OrderBillingDetail->delivery_country = !empty($billing['delivery_country']) ? $billing['delivery_country'] : '';
        $OrderBillingDetail->delivery_state = !empty($billing['delivery_state']) ? $billing['delivery_state'] : '';
        $OrderBillingDetail->save();

        // add in Order Coupon Detail table start
        if (!empty($requests_data['coupon_info'])) {
            $coupon_data = $requests_data['coupon_info'];
            $Coupon = Coupon::find($coupon_data['coupon_id']);
            // coupon stock decrease end

            // Order Coupon history
            $OrderCouponDetail = new OrderCouponDetail();
            $OrderCouponDetail->order_id = $order->id;
            $OrderCouponDetail->product_order_id = $order->product_order_id;
            $OrderCouponDetail->coupon_id = $coupon_data['coupon_id'];
            $OrderCouponDetail->coupon_name = $coupon_data['coupon_name'];
            $OrderCouponDetail->coupon_code = $coupon_data['coupon_code'];
            $OrderCouponDetail->coupon_discount_type = $coupon_data['coupon_discount_type'];
            $OrderCouponDetail->coupon_discount_number = $coupon_data['coupon_discount_number'];
            $OrderCouponDetail->coupon_discount_amount = $coupon_data['coupon_discount_amount'];
            $OrderCouponDetail->coupon_final_amount = $coupon_data['coupon_final_amount'];
            $OrderCouponDetail->theme_id = $theme_id;
            $OrderCouponDetail->save();

            // Coupon history
            $UserCoupon = new UserCoupon();
            $UserCoupon->user_id = !empty($requests_data['customer_id']) ? $requests_data['customer_id'] : null;
            $UserCoupon->coupon_id = $Coupon->id;
            $UserCoupon->amount = $coupon_data['coupon_discount_amount'];
            $UserCoupon->order_id = $order->id;
            $UserCoupon->date_used = now();
            $UserCoupon->theme_id = $theme_id;
            $UserCoupon->save();

            $discount_string = '-' . $coupon_data['coupon_discount_amount'];
            $CURRENCY = Utility::GetValueByName('CURRENCY', $store->theme_id, $store->id);
            $CURRENCY_NAME = Utility::GetValueByName('CURRENCY_NAME', $store->theme_id, $store->id);
            if ($coupon_data['coupon_discount_type'] == 'flat') {
                $discount_string .= $CURRENCY;
            } else {
                $discount_string .= '%';
            }

            $discount_string .= ' ' . __('for all products');
            $order_array['coupon']['code'] = $coupon_data['coupon_code'];
            $order_array['coupon']['discount_string'] = $discount_string;
            $order_array['coupon']['price'] = SetNumber($coupon_data['coupon_final_amount']);
        } elseif (!empty($requests_data['cartlist']['coupon_code'])) {
            $coupon_data = is_object($requests_data['cartlist']['coupon_info']) ? (array) $requests_data['cartlist']['coupon_info'] : $requests_data['cartlist']['coupon_info'];
            $Coupon = Coupon::find($coupon_data['coupon_id']);
            // coupon stock decrease end
            if ($Coupon) {
                // Order Coupon history
                $OrderCouponDetail = new OrderCouponDetail();
                $OrderCouponDetail->order_id = $order->id;
                $OrderCouponDetail->product_order_id = $order->product_order_id;
                $OrderCouponDetail->coupon_id = $coupon_data['coupon_id'];
                $OrderCouponDetail->coupon_name = $coupon_data['coupon_name'];
                $OrderCouponDetail->coupon_code = $coupon_data['coupon_code'];
                $OrderCouponDetail->coupon_discount_type = $coupon_data['coupon_discount_type'];
                $OrderCouponDetail->coupon_discount_number = $coupon_data['coupon_discount_number'];
                $OrderCouponDetail->coupon_discount_amount = $coupon_data['coupon_discount_amount'];
                $OrderCouponDetail->coupon_final_amount = $coupon_data['coupon_final_amount'];
                $OrderCouponDetail->theme_id = $theme_id;
                $OrderCouponDetail->save();

                // Coupon history
                $UserCoupon = new UserCoupon();
                $UserCoupon->user_id = !empty($request->customer_id) ? $request->customer_id : null;
                $UserCoupon->coupon_id = $Coupon->id;
                $UserCoupon->amount = $coupon_data['coupon_discount_amount'];
                $UserCoupon->order_id = $order->id;
                $UserCoupon->date_used = now();
                $UserCoupon->theme_id = $theme_id;
                $UserCoupon->save();
            }


            $discount_string = '-' . $coupon_data['coupon_discount_amount'];
            $CURRENCY = Utility::GetValueByName('CURRENCY');
            $CURRENCY_NAME = Utility::GetValueByName('CURRENCY_NAME');
            if ($coupon_data['coupon_discount_type'] == 'flat') {
                $discount_string .= $CURRENCY;
            } else {
                $discount_string .= '%';
            }

            $discount_string .= ' ' . __('for all products');
            $order_array['coupon']['code'] = $coupon_data['coupon_code'] ?? null;
            $order_array['coupon']['discount_string'] = $discount_string ?? null;
            $order_array['coupon']['price'] = SetNumber($coupon_data['coupon_final_amount'] ?? 0.00);
        }
        // add in Order Coupon Detail table end
        if (isset($requests_data['tax_id_value'])) {
            $taxes = TaxMethod::where('tax_id', $request['tax_id_value'])->where('theme_id', $theme_id)->where('store_id', $store->id)->orderBy('priority', 'asc')->get();
            $other_info = !is_array($requests_data['billing_info']) ? json_decode($requests_data['billing_info']) : $requests_data['billing_info'];
            $country = !empty($other_info->delivery_country) ? $other_info->delivery_country : '';
            $state_id = !empty($other_info->delivery_state) ? $other_info->delivery_state : '';
            $city_id = !empty($other_info->delivery_city) ? $other_info->delivery_city : '';
            foreach ($taxes as $tax) {
                $countryMatch = (!$tax->country_id || $country == $tax->country_id);
                $stateMatch = (!$tax->state_id || $state_id == $tax->state_id);
                $cityMatch = (!$tax->city_id || $city_id == $tax->city_id);

                if ($countryMatch && $stateMatch && $cityMatch) {
                    $OrderTaxDetail = new OrderTaxDetail();
                    $OrderTaxDetail->order_id = $order->id;
                    $OrderTaxDetail->product_order_id = $order->product_order_id;
                    $OrderTaxDetail->tax_id = $tax->id;
                    $OrderTaxDetail->tax_name = $tax->name;
                    $OrderTaxDetail->tax_discount_amount = $tax->tax_rate;
                    $OrderTaxDetail->tax_final_amount = $requests_data['tax_price'];
                    $OrderTaxDetail->theme_id = $theme_id;
                    $OrderTaxDetail->save();
                }
            }
        }

        //activity log
        ActivityLog::order_entry([
            'customer_id' => $order->customer_id,
            'order_id' => $order->product_order_id,
            'order_date' => $order->order_date,
            'products' => $order->product_id,
            'final_price' => $order->final_price,
            'payment_type' => $order->payment_type,
            'theme_id' => $order->theme_id,
            'store_id' => $order->store_id
        ]);
        $other_info = $request->billing_info;

        //Order Mail
        $order_email = $OrderBillingDetail->email ?? (!empty($other_info->email) ? $other_info->email : '');
        $owner = User::find($store->created_by);
        $owner_email = $owner->email;
        $order_id = Crypt::encrypt($order->id);

        if (module_is_active('PreOrder') && \Auth::guard('customers')->user() && isset($requests_data['order_type']) && $requests_data['order_type'] == 'pre_order') {
            \Workdo\PreOrder\app\Models\PreOrderHistory::PreOrderHistory($order, $store, $request, $order_email);
        } else {
            try {
                $dArr = [
                    'order_id' => $order->product_order_id,
                ];

                // Send Email
                $resp = Utility::sendEmailTemplate('Order Created', $order_email, $dArr, $owner, $store, $order_id);
                $resp1 = Utility::sendEmailTemplate('Order Created For Owner', $owner_email, $dArr, $owner, $store, $order_id);
            } catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }
        }

        foreach ($products as $product) {
            $product_data = Product::find($product->product_id);

            if ($product_data) {
                if ($product_data->variant_product == 0) {
                    if ($product_data->track_stock == 1) {
                        OrderNote::order_note_data([
                            'customer_id' => !empty($request->customer_id) ? $request->customer_id : '0',
                            'order_id' => $order->id,
                            'product_name' => !empty($product_data->name) ? $product_data->name : '',
                            'variant_product' => $product_data->variant_product,
                            'product_stock' => !empty($product_data->product_stock) ? $product_data->product_stock : '',
                            'status' => 'Stock Manage',
                            'theme_id' => $order->theme_id,
                            'store_id' => $order->store_id,
                        ]);
                    }
                } else {
                    $variant_data = ProductVariant::find($product->variant_id);
                    $variationOptions = explode(',', $variant_data->variation_option);
                    $option = in_array('manage_stock', $variationOptions);
                    if ($option == true) {
                        OrderNote::order_note_data([
                            'customer_id' => !empty($request->customer_id) ? $request->customer_id : '0',
                            'order_id' => !empty($order->id) ? $order->id : '',
                            'product_name' => !empty($product_data->name) ? $product_data->name : '',
                            'variant_product' => $product_data->variant_product,
                            'product_variant_name' => !empty($variant_data->variant) ? $variant_data->variant : '',
                            'product_stock' => !empty($variant_data->stock) ? $variant_data->stock : '',
                            'status' => 'Stock Manage',
                            'theme_id' => $order->theme_id,
                            'store_id' => $order->store_id,
                        ]);
                    }
                }
            }
        }

        OrderNote::order_note_data([
            'customer_id' => !empty($request->customer_id) ? $request->customer_id : '0',
            'order_id' => $order->id,
            'product_order_id' => $order->product_order_id,
            'delivery_status' => 'Pending',
            'status' => 'Order Created',
            'theme_id' => $order->theme_id,
            'store_id' => $order->store_id
        ]);
        try {
            $msg = __("Hello, Welcome to $store->name .Hi,your order id is $order->product_order_id, Thank you for Shopping We received your purchase request, we'll be in touch shortly!. ");
            $mess = Utility::SendMsgs('Order Created', $OrderBillingDetail->telephone, $msg);
        } catch (\Exception $e) {
            $smtp_error = __('Invalid OAuth access token - Cannot parse access token');
        }
        // add in Order Tax Detail table end
        event(new GetProductStatus($OrderBillingDetail, $order, $owner));
        if (auth('customers')->user() && module_is_active('ProductAffiliate')) {
            Utility::affiliateTransaction($order);
        }
        if (!empty($order) && !empty($OrderBillingDetail)) {

            $order_array['order_id'] = $order->id;
            $cart_array = [];
            $cart_json = json_encode($cart_array);
            Cookie::queue('cart', $cart_json, 1440);

            if (auth('customers')->user()) {
                event(new AddRewardClubPoint($requests_data, $order, $slug));
            }
            $msg = [
                'status' => 'success',
                'success' => __('Your Order Successfully Added'),
                'order_id' => $order->product_order_id,
                'data' => [
                    'order_id' => $order->id,
                ],
            ];
            return $msg;
        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'success' => __('Failed'),
                ]
            );
        }
    }
}
