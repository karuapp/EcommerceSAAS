<?php

namespace App\Http\Controllers;

use App\Models\{MainCategory, SubCategory};
use App\Models\ShopifyConection;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShopifySubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user() && auth()->user()->isAbleTo('Manage Shopify SubCategory')) {
            $setting = getAdminAllsetting();
            if(isset($setting['shopify_setting_enabled']) && $setting['shopify_setting_enabled'] == 'on')
            {
                try {
                    $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                    $shopify_store_url = \App\Models\Utility::GetValueByName('shopify_store_url', $theme_name, getCurrentStore());
                    $shopify_access_token = \App\Models\Utility::GetValueByName('shopify_access_token', $theme_name, getCurrentStore());
                    
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://$shopify_store_url.myshopify.com/admin/api/2023-07/custom_collections.json",
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
                        $sub_category = json_decode($response, true);
                        if (isset($sub_category['errors'])) {
                            $errorMessage = $sub_category['errors'];
                            return redirect()->back()->with('error', $errorMessage);
                        } else {
                            if (isset($sub_category) && !empty($sub_category)) {
                                $upddata = ShopifyConection::where('theme_id',$theme_name)->where('store_id',getCurrentStore())->where('module', 'sub_category')->pluck('shopify_id')->toArray();
                                return  view('shopify.sub_category', compact('sub_category', 'upddata'));
                            }
                        }
                    }
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
    public function store(Request $request, $id)
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
        
        if (auth()->user() && auth()->user()->isAbleTo('Create Shopify SubCategory')) {
            try {
                $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                $shopify_store_url = \App\Models\Utility::GetValueByName('shopify_store_url', $theme_name, getCurrentStore());
                $shopify_access_token = \App\Models\Utility::GetValueByName('shopify_access_token', $theme_name, getCurrentStore());
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://$shopify_store_url.myshopify.com/admin/api/2023-07/custom_collections.json?ids=$id",
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
                    $category = json_decode($response, true);

                    if (isset($category['errors'])) {

                        $errorMessage = $category['errors'];
                        return redirect()->back()->with('error', $errorMessage);
                    } else {
                        if (isset($category) && !empty($category)) {
                            $themeId = APP_THEME(); 
                            $storeId = getCurrentStore(); 
                            if (!empty($category['custom_collections'][0]['image']['src'])) {

                                $ImageUrl = $category['custom_collections'][0]['image']['src'];
                                $file_type = config('files_types');
                                $url = strtok($ImageUrl, '?');

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
                                $url  = asset(Storage::url('uploads/shopify.png'));
                                $name = 'shopify.png';
                                $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                                $path = 'themes/' . $themeId . '/uploads/';
                                $uplaod = Utility::upload_woo_file($url, $file2, $path);
                            }

                            
                            if (!empty($category)) {
                                $categoryQuery = MainCategory::query();
                                $subCategoryQuery = SubCategory::query();
                                $shopifyConectionQuery = ShopifyConection::query();
                                $existCategory = (clone $categoryQuery)->where('name',  $category['custom_collections'][0]['title'])->where('store_id', $storeId)->where('theme_id', $themeId)->first();

                                if (!$existCategory) {
                                    $existCategory = (clone $categoryQuery)->create([
                                        'name'       => $category['custom_collections'][0]['title'],
                                        'slug'       => 'collections/' . strtolower(preg_replace("/[^\w]+/", "-", $category['custom_collections'][0]['title'])),
                                        'image_url'  => $uplaod['full_url'] ?? null,
                                        'image_path' => $uplaod['url'] ?? null,
                                        'icon_path'  => $uplaod['url'] ?? null,
                                        'trending'   => 0,
                                        'status'     => 1,
                                        'theme_id'   => $themeId,
                                        'store_id'   => $storeId,
                                    ]);

                                    (clone $shopifyConectionQuery)->create([
                                        'module'       => 'category',
                                        'original_id'  => $existCategory->id,
                                        'shopify_id'   => $category['custom_collections'][0]['id'],
                                        'theme_id'     => $themeId,
                                        'store_id'     => $storeId,
                                    ]);
                                }
                                $existSubCategory = (clone $subCategoryQuery)->where('name',  $category['custom_collections'][0]['title'])->where('maincategory_id', $existCategory->id)->where('store_id', $storeId)->where('theme_id', $themeId)->first();
                                if (!$existSubCategory) {
                                    $existSubCategory = (clone $subCategoryQuery)->create([
                                        'name'       => $category['custom_collections'][0]['title'],
                                        'image_url'  => $uplaod['full_url'] ?? null,
                                        'image_path' => $uplaod['url'] ?? null,
                                        'icon_path'  => $uplaod['url'] ?? null,
                                        'maincategory_id'  =>$existCategory->id,
                                        'status'     => 1,
                                        'theme_id'   => $themeId,
                                        'store_id'   => $storeId,
                                    ]);

                                    (clone $shopifyConectionQuery)->create([
                                        'module'       => 'sub_category',
                                        'original_id'  => $existSubCategory->id,
                                        'shopify_id'   => $category['custom_collections'][0]['id'],
                                        'theme_id'     => $themeId,
                                        'store_id'     => $storeId,
                                    ]);
                                }
                                return redirect()->back()->with('success', __('SubCategory successfully add.'));
                            } else {
                                return redirect()->back()->with('error', __('SubCategory Not Found.'));
                            }
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
        
        if (auth()->user() && auth()->user()->isAbleTo('Edit Shopify SubCategory')) {
            try {
                $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                $shopify_store_url = \App\Models\Utility::GetValueByName('shopify_store_url', $theme_name, getCurrentStore());
                $shopify_access_token = \App\Models\Utility::GetValueByName('shopify_access_token', $theme_name, getCurrentStore());
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://$shopify_store_url.myshopify.com/admin/api/2023-07/custom_collections.json?ids=$id",
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
                    $category = json_decode($response, true);

                    if (isset($category['errors'])) {

                        $errorMessage = $category['errors'];
                        return redirect()->back()->with('error', $errorMessage);
                    } else {
                        $themeId = APP_THEME(); 
                        $storeId = getCurrentStore(); 
                        $shopifyConectionQuery = ShopifyConection::query();
                        $categoryData = (clone $shopifyConectionQuery)->where('theme_id', $theme_name)->where('store_id', $storeId)->where('module', 'category')->where('shopify_id', $id)->first();

                        $category_id = $categoryData->original_id;
                        $subCategoryData = (clone $shopifyConectionQuery)->where('theme_id', $theme_name)->where('store_id', $storeId)->where('module', 'sub_category')->where('shopify_id', $id)->first();

                        $sub_category_id = $subCategoryData->original_id;

                        if (!empty($category['custom_collections'][0]['image']['src'])) {

                            $ImageUrl = $category['custom_collections'][0]['image']['src'];
                            $file_type = config('files_types');
                            $url = strtok($ImageUrl, '?');

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
                            $url  = asset(Storage::url('uploads/shopify.png'));
                            $name = 'shopify.png';
                            $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                            $path = 'themes/' . $themeId . '/uploads/';
                            $uplaod = Utility::upload_woo_file($url, $file2, $path);
                        }
                        if (!empty($category)) {
                            $existCategory = MainCategory::find($category_id);
                            if ($existCategory) {
                                $existCategory->name         = $category['custom_collections'][0]['title'];
                                $existCategory->image_url    = $uplaod['full_url'] ?? null;
                                $existCategory->image_path   = $uplaod['url'] ?? null;
                                $existCategory->icon_path    = $uplaod['url'] ?? null;
                                $existCategory->trending     = 0;
                                $existCategory->status       = 1;
                                $existCategory->save();
                            }
                            $existSubCategory= SubCategory::find($sub_category_id);
                            if ($existSubCategory) {
                                $existSubCategory->name         = $category['custom_collections'][0]['title'];
                                $existSubCategory->image_url    = $uplaod['full_url'] ?? null;
                                $existSubCategory->image_path   = $uplaod['url'] ?? null;
                                $existSubCategory->icon_path    = $uplaod['url'] ?? null;
                                $existSubCategory->status       = 1;
                                $existSubCategory->save();
                            }
                            

                            return redirect()->back()->with('success', __('SubCategory successfully update.'));
                        } else {
                            return redirect()->back()->with('error', __('SubCategory Not Found.'));
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
