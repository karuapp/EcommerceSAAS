<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\{MainCategory, SubCategory};
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShopifyConection;
use App\Models\Customer;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Plan;

class ShopifyProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Manage Shopify Product')) {
            $setting = getAdminAllsetting();
            if (isset($setting['shopify_setting_enabled']) && $setting['shopify_setting_enabled'] == 'on') {
                try {
                    $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                    $shopify_store_url = \App\Models\Utility::GetValueByName('shopify_store_url', $theme_name, getCurrentStore());
                    $shopify_access_token = \App\Models\Utility::GetValueByName('shopify_access_token', $theme_name, getCurrentStore());

                    $limit = $request->input('limit', 10);
                    $page_info = $request->input('page_info', null);
                    $previous_page_info = $request->input('previous_page_info', null);

                    $pagination = $this->fetchProducts($shopify_store_url, $shopify_access_token, $limit, $page_info, $previous_page_info);

                    $products = $pagination['products'];
                    $next_page_info = $pagination['next_page_info'];
                    $prev_page_info = $pagination['prev_page_info'];

                    $count_curl = curl_init();
                    curl_setopt_array($count_curl, array(
                        CURLOPT_URL => "https://$shopify_store_url.myshopify.com/admin/api/2023-07/products/count.json",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => array(
                            "X-Shopify-Access-Token: $shopify_access_token"
                        ),
                    ));

                    $count_response = curl_exec($count_curl);
                    curl_close($count_curl);

                    if ($count_response === false) {
                        return redirect()->back()->with('error', 'Failed to fetch product count.');
                    }

                    $count_data = json_decode($count_response, true);
                    $total_products = $count_data['count'];

                    $upddata = ShopifyConection::where('theme_id', $theme_name)
                        ->where('store_id', getCurrentStore())
                        ->where('module', '=', 'product')
                        ->pluck('shopify_id')
                        ->toArray();

                    return view('shopify.product', compact('products', 'upddata', 'total_products', 'limit', 'page_info', 'next_page_info', 'prev_page_info'));
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Something went wrong.');
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function fetchProducts($shopify_store_url, $shopify_access_token, $limit, $page_info, $previous_page_info)
    {
        $products = [];
        $next_page_info = null;
        $prev_page_info = null;

        $url = "https://$shopify_store_url.myshopify.com/admin/api/2023-07/products.json?limit=$limit&fields=id,title,product_type,variants,image";

        if ($page_info) {
            $url .= "&page_info=$page_info";
        } elseif ($previous_page_info) {
            $url .= "&page_info=$previous_page_info";
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "X-Shopify-Access-Token: $shopify_access_token"
            ),
            CURLOPT_HEADER => true,
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception('Something went wrong while fetching products.');
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        curl_close($curl);

        $page_data = json_decode($body, true);

        if (isset($page_data['errors'])) {
            throw new \Exception($page_data['errors']);
        }

        $products = $page_data['products'];

        $next_page_info = $this->getNextPageInfo($header, 'next');
        $prev_page_info = $this->getNextPageInfo($header, 'previous');

        return [
            'products' => $products,
            'next_page_info' => $next_page_info,
            'prev_page_info' => $prev_page_info,
        ];
    }

    private function getNextPageInfo($header, $direction)
    {
        $pattern = $direction === 'next' ? '/<([^>]*)>; rel="next"/' : '/<([^>]*)>; rel="previous"/';
        if (preg_match($pattern, $header, $matches)) {
            $url = $matches[1];
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            return $query['page_info'] ?? null;
        }
        return null;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (auth()->user() && auth()->user()->isAbleTo('Create Shopify Product')) {
            try {
                $user = auth()->user();
                $creator = User::find($user->creatorId());
                $total_products = $user->countProducts();
                $plan = Plan::find($creator->plan_id);
                if (($total_products >= $plan->max_products) && ($plan->max_products != -1)) {
                    return redirect()->back()->with('error', __('Your Product limit is over, Please upgrade plan'));
                }
                $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                $store_id = Store::where('id', getCurrentStore())->first();
                $shopify_store_url = \App\Models\Utility::GetValueByName('shopify_store_url', $theme_name, getCurrentStore());
                $shopify_access_token = \App\Models\Utility::GetValueByName('shopify_access_token', $theme_name, getCurrentStore());

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://$shopify_store_url.myshopify.com/admin/api/2023-07/products.json?ids=$id",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        "X-Shopify-Access-Token: $shopify_access_token"
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                if ($response == false) {
                    return redirect()->back()->with('error', 'Something went wrong.');
                } else {
                    $themeId = APP_THEME();
                    $storeId = getCurrentStore();

                    $maincategory = MainCategory::where('theme_id', $themeId)->where('store_id', $storeId)->first();
                    $subcategory = SubCategory::where('maincategory_id', $maincategory->id)->where('theme_id', $themeId)->where('store_id', $storeId)->first();

                    $products = json_decode($response, true);

                    $upddata = ShopifyConection::where('theme_id', $theme_name)->where('store_id', $storeId)->where('module', '=', 'category')->where('shopify_id', $products['products'][0]['product_type'])->pluck('shopify_id')->first();

                    if (isset($products['errors'])) {

                        $errorMessage = $products['errors'];
                        return redirect()->back()->with('error', $errorMessage);
                    } else {

                        if (isset($products) && !empty($products)) {


                            if (!empty($products['products'][0]['image']['src'])) {
                                $ImageUrl = $products['products'][0]['image']['src'];
                                $url =  strtok($ImageUrl, '?');
                                $file_type = config('files_types');

                                foreach ($file_type as $f) {
                                    $name = basename($url, "." . $f);
                                }
                                $file_size = 0;
                                try{
                                    $file_url = str_replace("\0", '', $url);
                                    $get_file = \Illuminate\Support\Facades\Http::head($file_url);
                                    $file_size = $get_file->header('Content-Length');
                                } catch(\Exception $e)
                                {
                                    return redirect()->back()->with('error', $e);
                                }
                                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $file_size);
                                if ($result != 1) {
                                    return redirect()->back()->with('error', $result);
                                }
                                $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                $path = 'themes/' . $themeId . '/uploads/';
                                $uplaod = Utility::upload_woo_file($url, $file2, $path);
                            } else {

                                $url    = asset(Storage::url('uploads/woocommerce.png'));
                                $name   = 'woocommerce.png';
                                $file2  = rand(10, 100) . '_' . time() . "_" . $name;
                                $path   = 'themes/' . $themeId . '/uploads/';
                                $uplaod = Utility::upload_woo_file($url, $file2, $path);
                            }
                            $product                          = new Product();
                            $product->name                    = $products['products'][0]['title'];
                            $product->description             = strip_tags($products['products'][0]['body_html']);
                            $product->cover_image_path      = $uplaod['url'];
                            $product->cover_image_url       = $uplaod['full_url'];
                            if ($products['products'][0]['variants'][0]['title'] == 'Default Title') {
                                $product->product_weight          = $products['products'][0]['variants'][0]['weight'];
                            }
                            $product->maincategory_id             = $maincategory->id;
                            $product->subcategory_id              = $subcategory->id;

                            if ($products['products'][0]['variants'][0]['title'] == 'Default Title') {
                                $product->variant_product = 0;
                            } else {
                                $product->variant_product = 1;
                            }
                            $product->slug    = str_replace(' ', '_', strtolower($products['products'][0]['title']));
                            $product->status = 1;
                            if ($products['products'][0]['variants'][0]['title'] == 'Default Title') {
                                $product->track_stock = 1;
                                $product->stock_order_status = 'not_allow';
                                $product->price = $products['products'][0]['variants'][0]['price'];
                                $product->product_stock = $products['products'][0]['variants'][0]['inventory_quantity'];
                            }
                            $product->track_stock = 1;
                            $product->theme_id              = $themeId;
                            $product->store_id              = $storeId;
                            $product->created_by            = auth()->user()->id;
                            $attribute_id = [];


                            $option_attribute_value = [];
                            foreach ($products['products'][0]['options'] as $option) {
                                $option_attribute_value[] = $option['values'];
                            }
                            $mergedArray = [];
                            foreach ($option_attribute_value as $array) {
                                $mergedArray = array_merge($mergedArray, $array);
                            }
                            $options_value_mergedArray = array_map(function ($element) {
                                return str_replace(' ', '', $element);
                            }, $mergedArray);


                            if ($products['products'][0]['variants'][0]['title'] != 'Default Title') {
                                foreach ($products['products'][0]['options'] as $option) {
                                    $product_Attrybute = ProductAttribute::where('name', $option['name'])->where('theme_id', $theme_name)->where('store_id', $storeId)->first();
                                    $slug = User::slugs($option['name']);

                                    if (!empty($product_Attrybute->name) != $option['name']) {
                                        $attribute                      = new ProductAttribute();

                                        $attribute->name                = $option['name'];
                                        $attribute->slug                = $slug;
                                        $attribute->theme_id            = $themeId;
                                        $attribute->store_id            = $storeId;
                                        $attribute->save();
                                    }

                                    foreach ($option['values'] as $ProductAttribute) {
                                        $title = str_replace(' ', '', $ProductAttribute);
                                        $product_AttributeOption = ProductAttributeOption::where('terms', $title)->where('theme_id', $theme_name)->where('store_id', $storeId)->first();
                                        if (!empty($product_AttributeOption->terms) != $title) {
                                            $attribute_option                      = new ProductAttributeOption();
                                            $attribute_option->attribute_id        = !empty($attribute->id) ? $attribute->id : $product_Attrybute->id;
                                            $attribute_option->terms               = $title;
                                            $attribute_option->theme_id            = $themeId;
                                            $attribute_option->store_id            = $storeId;
                                            $attribute_option->save();
                                        }
                                    }
                                    if (!empty($attribute)) {
                                        $attribute_id[] = $attribute->id;
                                    } else {
                                        $attribute_id[] = $product_Attrybute->id;
                                    }
                                }

                                $product->attribute_id = json_encode($attribute_id);
                                $attribute_options = [];
                                $options_value = array_map(function ($element) {
                                    return str_replace(' ', '', $element);
                                }, $option['values']);
                                $attribute_option_terms = ProductAttributeOption::whereIn('attribute_id', $attribute_id)->whereIn('terms', $options_value_mergedArray)->pluck('terms')->toArray();
                                foreach ($attribute_id as $key => $no) {


                                    $conditionMet = false;

                                    foreach ($options_value_mergedArray as $ProductAttribute) {
                                        if (in_array($ProductAttribute, $attribute_option_terms)) {
                                            $conditionMet = true;
                                            break;
                                        }
                                    }
                                    if ($conditionMet) {
                                        $attribute_option_id = ProductAttributeOption::where('attribute_id', $no)->whereIn('terms', $options_value_mergedArray)->pluck('id')->toArray();
                                    } else {
                                        $attribute_option_id = ProductAttributeOption::where('attribute_id', $no)->pluck('id')->toArray();
                                    }

                                    $enable_option = 1;
                                    $variation_option = 1;
                                    $item['attribute_id'] = $no;

                                    $item['values'] = explode(',', implode('|', $attribute_option_id));

                                    $item['visible_attribute_' . $no] = $enable_option;
                                    $item['for_variation_' . $no] = $variation_option;
                                    array_push($attribute_options, $item);
                                }
                                $attribute_options = json_encode($attribute_options);
                                $product->product_attribute = $attribute_options;
                            }


                            $product->save();

                            if ($products['products'][0]['variants'][0]['title'] != 'Default Title') {

                                foreach ($products['products'][0]['variants'] as $variants) {
                                    $title_spase = str_replace(' / ', '-', $variants['title']);
                                    $title = str_replace(' ', '', $title_spase);

                                    $sku = str_replace(' ', '_', $product->name . '-' . $title);
                                    $productVariant                 = new ProductVariant();
                                    $productVariant->product_id     = $product->id;
                                    $productVariant->variant        = $title;
                                    $productVariant->sku            = $sku;
                                    $productVariant->stock          = $variants['inventory_quantity'];
                                    $productVariant->price          = $variants['price'];
                                    $productVariant->variation_price = $variants['price'];
                                    $productVariant->stock_order_status = 'not_allow';
                                    $productVariant->variation_option = 'manage_stock';
                                    $productVariant->variation_option = 'manage_stock';
                                    $productVariant->theme_id            = $themeId;
                                    $productVariant->store_id            = $storeId;
                                    $productVariant->save();
                                }
                            }

                            if (empty($products['products'][0]['images'][0])) {
                                $url  = asset(Storage::url('uploads/woocommerce.png'));
                                $name = 'woocommerce.png';
                                $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                $path = 'themes/' . $themeId . '/uploads/';
                                $ulpaod = Utility::upload_woo_file($url, $file2, $path);

                                $ProductImage = new ProductImage();
                                $ProductImage->product_id = $product->id;

                                $ProductImage->image_path = $ulpaod['url'];
                                $ProductImage->image_url  = $ulpaod['full_url'];
                                $ProductImage->theme_id   = $store_id->theme_id;
                                $ProductImage->store_id   = $storeId;
                                $ProductImage->save();
                            } else {
                                for ($i = 1; $i < count($products['products'][0]['images']); $i++) {
                                    $image = $products['products'][0]['images'][$i];
                                    $id = $image['id'];
                                    $dateCreated = $image['created_at'];
                                    $src = $image['src'];

                                    $ImageUrl = $src;
                                    $url =  strtok($ImageUrl, '?');

                                    $file_type = config('files_types');

                                    foreach ($file_type as $f) {
                                        $name = basename($url, "." . $f);
                                    }
                                    $file_size = 0;
                                    try{
                                        $file_url = str_replace("\0", '', $url);
                                        $get_file = \Illuminate\Support\Facades\Http::head($file_url);
                                        $file_size = $get_file->header('Content-Length');
                                    } catch(\Exception $e)
                                    {
                                        return redirect()->back()->with('error', $e);
                                    }
                                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $file_size);
                                    if ($result != 1) {
                                        return redirect()->back()->with('error', $result);
                                    }
                                    $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                    $path = 'themes/' . $themeId . '/uploads/';
                                    $subimg = Utility::upload_woo_file($url, $file2, $path);

                                    $ProductImage = new ProductImage();
                                    $ProductImage->product_id = $product->id;

                                    $ProductImage->image_path = $subimg['url'];
                                    $ProductImage->image_url  = $subimg['full_url'];
                                    $ProductImage->theme_id   = $store_id->theme_id;
                                    $ProductImage->store_id   = $storeId;
                                    $ProductImage->save();
                                }
                            }

                            $products_connection                    = new ShopifyConection();
                            $products_connection->store_id          = $storeId;
                            $products_connection->theme_id          = $themeId;
                            $products_connection->module            = 'product';
                            $products_connection->shopify_id        = $products['products'][0]['id'];
                            $products_connection->original_id       = $product->id;
                            $products_connection->save();





                            return redirect()->back()->with('success', 'Product successfully add.');
                        }
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Something went wrong.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (auth()->user() && auth()->user()->isAbleTo('Edit Shopify Product')) {
            try {
                $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                $store_id = Store::where('id', getCurrentStore())->first();
                $shopify_store_url = \App\Models\Utility::GetValueByName('shopify_store_url', $theme_name, getCurrentStore());
                $shopify_access_token = \App\Models\Utility::GetValueByName('shopify_access_token', $theme_name, getCurrentStore());

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://$shopify_store_url.myshopify.com/admin/api/2023-07/products.json?ids=$id",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        "X-Shopify-Access-Token: $shopify_access_token"
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);

                if ($response == false) {
                    return redirect()->back()->with('error', 'Something went wrong.');
                } else {
                    $products = json_decode($response, true);
                    if (isset($products['errors'])) {

                        $errorMessage = $products['errors'];
                        return redirect()->back()->with('error', $errorMessage);
                    } else {
                        $themeId = APP_THEME();
                        $storeId = getCurrentStore();
                        $mainCategory = MainCategory::where('theme_id', $themeId)->where('store_id', $storeId)->first();
                        $subCategory = SubCategory::where('maincategory_id', $mainCategory->id)->where('theme_id', $themeId)->where('store_id', $storeId)->first();
                        if (isset($products) && !empty($products)) {

                            if (!empty($products['products'][0]['image']['src'])) {
                                $ImageUrl = $products['products'][0]['image']['src'];
                                $url =  strtok($ImageUrl, '?');
                                $file_type = config('files_types');

                                foreach ($file_type as $f) {
                                    $name = basename($url, "." . $f);
                                }
                                $file_size = 0;
                                try{
                                    $file_url = str_replace("\0", '', $url);
                                    $get_file = \Illuminate\Support\Facades\Http::head($file_url);
                                    $file_size = $get_file->header('Content-Length');
                                } catch(\Exception $e)
                                {
                                    return redirect()->back()->with('error', $e);
                                }
                                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $file_size);
                                if ($result != 1) {
                                    return redirect()->back()->with('error', $result);
                                }
                                $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                $path = 'themes/' . $themeId . '/uploads/';
                                $uplaod = Utility::upload_woo_file($url, $file2, $path);
                            } else {

                                $url    = asset(Storage::url('uploads/woocommerce.png'));
                                $name   = 'woocommerce.png';
                                $file2  = rand(10, 100) . '_' . time() . "_" . $name;
                                $path   = 'themes/' . $themeId . '/uploads/';
                                $uplaod = Utility::upload_woo_file($url, $file2, $path);
                            }
                            $upddata = ShopifyConection::where('theme_id', $store_id->theme_id)->where('store_id', $storeId)->where('module', '=', 'product')->where('shopify_id', $id)->first();
                            $original_id = $upddata->original_id;

                            $product                          = Product::find($original_id);
                            $product->name                    = $products['products'][0]['title'];
                            $product->description             = strip_tags($products['products'][0]['body_html']);
                            $product->cover_image_path        = $uplaod['url'];
                            $product->cover_image_url         = $uplaod['full_url'];
                            if ($products['products'][0]['variants'][0]['title'] == 'Default Title') {
                                $product->product_weight          = $products['products'][0]['variants'][0]['weight'];
                            }
                            $product->maincategory_id             = $mainCategory->id;
                            $product->subcategory_id             = $subCategory->id;

                            if ($products['products'][0]['variants'][0]['title'] == 'Default Title') {
                                $product->variant_product = 0;
                            } else {
                                $product->variant_product = 1;
                            }
                            $product->slug    = str_replace(' ', '_', strtolower($products['products'][0]['title']));
                            $product->status = 1;
                            if ($products['products'][0]['variants'][0]['title'] == 'Default Title') {
                                $product->track_stock = 1;
                                $product->stock_order_status = 'not_allow';
                                $product->price = $products['products'][0]['variants'][0]['price'];
                                $product->product_stock = $products['products'][0]['variants'][0]['inventory_quantity'];
                            }
                            $product->track_stock = 1;
                            $attribute_id = [];


                            $option_attribute_value = [];
                            foreach ($products['products'][0]['options'] as $option) {
                                $option_attribute_value[] = $option['values'];
                            }
                            $mergedArray = [];
                            foreach ($option_attribute_value as $array) {
                                $mergedArray = array_merge($mergedArray, $array);
                            }
                            $options_value_mergedArray = array_map(function ($element) {
                                return str_replace(' ', '', $element);
                            }, $mergedArray);




                            if ($products['products'][0]['variants'][0]['title'] != 'Default Title') {
                                foreach ($products['products'][0]['options'] as $option) {
                                    $product_Attrybute = ProductAttribute::where('name', $option['name'])->where('theme_id', $theme_name)->where('store_id', $storeId)->first();
                                    $slug = User::slugs($option['name']);

                                    if (!empty($product_Attrybute->name) != $option['name']) {
                                        $attribute                      = new ProductAttribute();

                                        $attribute->name                = $option['name'];
                                        $attribute->slug                = $slug;
                                        $attribute->theme_id            = $themeId;
                                        $attribute->store_id            = $storeId;
                                        $attribute->save();
                                    }

                                    foreach ($option['values'] as $ProductAttribute) {
                                        $title = str_replace(' ', '', $ProductAttribute);
                                        $product_AttributeOption = ProductAttributeOption::where('terms', $title)->where('theme_id', $theme_name)->where('store_id', $storeId)->first();
                                        if (!empty($product_AttributeOption->terms) != $title) {
                                            $attribute_option                      = new ProductAttributeOption();
                                            $attribute_option->attribute_id        = !empty($attribute->id) ? $attribute->id : $product_Attrybute->id;
                                            $attribute_option->terms               = $title;
                                            $attribute_option->theme_id            = $themeId;
                                            $attribute_option->store_id            = $storeId;
                                            $attribute_option->save();
                                        }
                                    }
                                    if (!empty($attribute)) {
                                        $attribute_id[] = $attribute->id;
                                    } else {
                                        $attribute_id[] = $product_Attrybute->id;
                                    }
                                }

                                $product->attribute_id = json_encode($attribute_id);
                                $attribute_options = [];
                                $options_value = array_map(function ($element) {
                                    return str_replace(' ', '', $element);
                                }, $option['values']);
                                $attribute_option_terms = ProductAttributeOption::whereIn('attribute_id', $attribute_id)->whereIn('terms', $options_value_mergedArray)->pluck('terms')->toArray();
                                foreach ($attribute_id as $key => $no) {


                                    $conditionMet = false;

                                    foreach ($options_value_mergedArray as $ProductAttribute) {
                                        if (in_array($ProductAttribute, $attribute_option_terms)) {
                                            $conditionMet = true;
                                            break;
                                        }
                                    }
                                    if ($conditionMet) {
                                        $attribute_option_id = ProductAttributeOption::where('attribute_id', $no)->whereIn('terms', $options_value_mergedArray)->pluck('id')->toArray();
                                    } else {
                                        $attribute_option_id = ProductAttributeOption::where('attribute_id', $no)->pluck('id')->toArray();
                                    }

                                    $enable_option = 1;
                                    $variation_option = 1;
                                    $item['attribute_id'] = $no;

                                    $item['values'] = explode(',', implode('|', $attribute_option_id));

                                    $item['visible_attribute_' . $no] = $enable_option;
                                    $item['for_variation_' . $no] = $variation_option;
                                    array_push($attribute_options, $item);
                                }
                                $attribute_options = json_encode($attribute_options);
                                $product->product_attribute = $attribute_options;
                            }


                            $product->save();

                            if ($products['products'][0]['variants'][0]['title'] != 'Default Title') {
                                foreach ($products['products'][0]['variants'] as $variants) {
                                    $productVariant = ProductVariant::where('product_id', $product->id)->get();
                                    $title_spase = str_replace(' / ', '-', $variants['title']);
                                    $title = str_replace(' ', '', $title_spase);

                                    $sku = str_replace(' ', '_', $product->name . '-' . $title);
                                    foreach ($productVariant as $stock) {
                                        if ($stock['variant'] != $title) {
                                            $stock->delete();
                                        }
                                    }
                                }
                                foreach ($products['products'][0]['variants'] as $variants) {
                                    $title_spase = str_replace(' / ', '-', $variants['title']);
                                    $title = str_replace(' ', '', $title_spase);
                                    $sku = str_replace(' ', '_', $product->name . '-' . $title);
                                    $productVariant = ProductVariant::where('product_id', $product->id)->get();


                                    $productVariant = ProductVariant::where('product_id', $product->id)->where('variant', $title)->first();

                                    if ($productVariant != null) {
                                        $productVariant->variant        = $title;
                                        $productVariant->sku            = $sku;
                                        $productVariant->stock          = $variants['inventory_quantity'];
                                        $productVariant->price          = $variants['price'];
                                        $productVariant->variation_price = $variants['price'];
                                    }
                                    if ($productVariant == null) {
                                        $productVariant = new ProductVariant;
                                        $productVariant->product_id = $product->id;
                                        $productVariant->product_id     = $product->id;
                                        $productVariant->variant        = $title;
                                        $productVariant->sku            = $sku;
                                        $productVariant->stock          = $variants['inventory_quantity'];
                                        $productVariant->price          = $variants['price'];
                                        $productVariant->variation_price = $variants['price'];
                                        $productVariant->stock_order_status = 'not_allow';
                                        $productVariant->variation_option = 'manage_stock';
                                        $productVariant->variation_option = 'manage_stock';
                                        $productVariant->theme_id            = $themeId;
                                        $productVariant->store_id            = $storeId;
                                        $productVariant->save();
                                    }
                                }
                            }
                            $ProductImage = ProductImage::where('product_id', $product->id)->where('theme_id', $theme_name)->where('store_id', $storeId)->first();
                            if (empty($products['products'][0]['images'][0])) {
                                $url  = asset(Storage::url('uploads/woocommerce.png'));
                                $name = 'woocommerce.png';
                                $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                $path = 'themes/' . $themeId . '/uploads/';
                                $ulpaod = Utility::upload_woo_file($url, $file2, $path);

                                $ProductImage->product_id = $product->id;

                                $ProductImage->image_path = $ulpaod['url'];
                                $ProductImage->image_url  = $ulpaod['full_url'];
                                $ProductImage->theme_id   = $store_id->theme_id;
                                $ProductImage->store_id   = $storeId;
                                $ProductImage->save();
                            } else {
                                for ($i = 1; $i < count($products['products'][0]['images']); $i++) {
                                    $image = $products['products'][0]['images'][$i];
                                    $id = $image['id'];
                                    $dateCreated = $image['created_at'];
                                    $src = $image['src'];

                                    $ImageUrl = $src;
                                    $url =  strtok($ImageUrl, '?');

                                    $file_type = config('files_types');

                                    foreach ($file_type as $f) {
                                        $name = basename($url, "." . $f);
                                    }
                                    $file_size = 0;
                                    try{
                                        $file_url = str_replace("\0", '', $url);
                                        $get_file = \Illuminate\Support\Facades\Http::head($file_url);
                                        $file_size = $get_file->header('Content-Length');
                                    } catch(\Exception $e)
                                    {
                                        return redirect()->back()->with('error', $e);
                                    }
                                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $file_size);
                                    if ($result != 1) {
                                        return redirect()->back()->with('error', $result);
                                    }
                                    $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                    $path = 'themes/' . $themeId . '/uploads/';
                                    $subimg = Utility::upload_woo_file($url, $file2, $path);

                                    $ProductImage->product_id = $product->id;

                                    $ProductImage->image_path = $subimg['url'];
                                    $ProductImage->image_url  = $subimg['full_url'];
                                    $ProductImage->theme_id   = $store_id->theme_id;
                                    $ProductImage->store_id   = $storeId;
                                    $ProductImage->save();
                                }
                            }

                            return redirect()->back()->with('success', 'Product successfully update.');
                        }
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Something went wrong.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
