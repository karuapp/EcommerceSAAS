<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Utility;
use App\Models\WoocommerceConection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Codexshaper\WooCommerce\Facades\Category;
use Xendit\Xendit;
use Yajra\DataTables\Facades\DataTables;

class WoocomSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user() && auth()->user()->isAbleTo('Manage Woocommerce SubCategory'))
        {
            $setting = getAdminAllsetting();
            if(isset($setting['woocommerce_setting_enabled']) && $setting['woocommerce_setting_enabled'] == 'on')
            {
                try{
                    $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                    $woocommerce_store_url =Utility::GetValueByName('woocommerce_store_url',$theme_name, getCurrentStore());
                    $woocommerce_consumer_secret =Utility::GetValueByName('woocommerce_consumer_secret',$theme_name, getCurrentStore());
                    $woocommerce_consumer_key =Utility::GetValueByName('woocommerce_consumer_key',$theme_name, getCurrentStore());

                    config(['woocommerce.store_url' => $woocommerce_store_url]);
                    config(['woocommerce.consumer_key' => $woocommerce_consumer_key]);
                    config(['woocommerce.consumer_secret' => $woocommerce_consumer_secret]);

                    // Fetch all products using pagination
                    $jsonData = collect(); // Initialize an empty collection
                    $page = 1;
                    $perPage = 100;
                    do {
                        // Fetch products from WooCommerce API, with pagination
                        $categories = Category::all(['per_page' => $perPage, 'page' => $page]);
                        $categories = $categories->filter(function ($category) {
                            return $category->parent !== 0;
                        });
                        $jsonData = $jsonData->merge($categories); // Append the new page data to the collection
                        $page++;
                    } while (count($categories) > 0); // Continue fetching until no products are returned

                    // Check if the request is for datatable
                    if (request()->ajax()) {
                        $upddata = WoocommerceConection::where('theme_id',APP_THEME())->where('store_id',getCurrentStore())->where('module','=','category')->get()->pluck('woocomerce_id')->toArray();
                        return DataTables::of($jsonData)
                            ->addColumn('cover_image', function ($data) {
                                $imgSrc = !empty($data->image) ? get_file($data->image->src, APP_THEME()) : asset(Storage::url('uploads/woocommerce.png'));
                                return '<img src="' . $imgSrc . '" alt="" width="100" class="cover_img">';
                            })
                            ->addColumn('action', function ($data) use ($upddata) {
                                return view('woocommerce.sub_category_action', compact('data','upddata'));
                            })
                            ->rawColumns(['cover_image','name','action'])
                            ->make(true);
                    }

                    return view('woocommerce.sub_category');

                }
                catch(\Exception $e){
                    return redirect()->back()->with('error' , 'Something went wrong.');
                }
            }else{
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
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
    public function store(Request $request)
    {
        $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
        $woocommerce_store_url =Utility::GetValueByName('woocommerce_store_url',$theme_name, getCurrentStore());
        $response = \Http::get($woocommerce_store_url . '/wp-json/wc/v3/products/categories');
        $jsonData = $response->json();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            if (auth()->user() && auth()->user()->isAbleTo('Create Woocommerce SubCategory')) {
                $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                $woocommerce_store_url = Utility::GetValueByName('woocommerce_store_url', $theme_name, getCurrentStore());
                $woocommerce_consumer_secret = Utility::GetValueByName('woocommerce_consumer_secret', $theme_name, getCurrentStore());
                $woocommerce_consumer_key = Utility::GetValueByName('woocommerce_consumer_key', $theme_name, getCurrentStore());
    
                config(['woocommerce.store_url' => $woocommerce_store_url]);
                config(['woocommerce.consumer_key' => $woocommerce_consumer_key]);
                config(['woocommerce.consumer_secret' => $woocommerce_consumer_secret]);
    
                $jsonData = Category::find($id);
                if (!$jsonData) {
                    return redirect()->back()->with('error', __('Category Not Found.'));
                }
    
                if (isset($jsonData['parent'])) {
                    $exist = WoocommerceConection::where('module', 'category')->where('woocomerce_id', $jsonData['parent'])->first();
                    if (!$exist) {
                        return redirect()->back()->with('error', __('This Sub Category Main Category Not Synced. Please First Create Sync Main Category.'));
                    }
                }
    
                if (!empty($jsonData['image']->src)) {
                    $url = $jsonData['image']->src;
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
                    $path = 'themes/' . APP_THEME() . '/uploads/';
                    $uplaod = Utility::upload_woo_file($url, $file2, $path);
                } else {
                    $url = asset(Storage::url('uploads/woocommerce.png'));
                    $name = 'woocommerce.png';
                    $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                    $path = 'themes/' . APP_THEME() . '/uploads/';
                    $uplaod = Utility::upload_woo_file($url, $file2, $path);
                }
    
                if (!empty($uplaod)) {
                    $subCategory = new SubCategory();
                    $subCategory->name = $jsonData['name'];
                    $subCategory->maincategory_id = $exist->original_id ?? 0;
                    $subCategory->image_url = $uplaod['full_url'];
                    $subCategory->image_path = $uplaod['url'];
                    $subCategory->icon_path = $uplaod['url'];
                    $subCategory->status = 1;
                    $subCategory->theme_id = APP_THEME();
                    $subCategory->store_id = getCurrentStore();
                    $subCategory->save();
    
                    $Category = new WoocommerceConection();
                    $Category->store_id = getCurrentStore();
                    $Category->theme_id = APP_THEME();
                    $Category->module = 'sub_category';
                    $Category->woocomerce_id = $jsonData['id'];
                    $Category->original_id = $subCategory->id;
                    $Category->save();
    
                    return redirect()->back()->with('success', __('Category successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Image upload failed.'));
                }
    
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
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
        try {
            if (auth()->user() && auth()->user()->isAbleTo('Edit Woocommerce SubCategory')) {
                $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
                $woocommerce_store_url = Utility::GetValueByName('woocommerce_store_url', $theme_name, getCurrentStore());
                $woocommerce_consumer_secret = Utility::GetValueByName('woocommerce_consumer_secret', $theme_name, getCurrentStore());
                $woocommerce_consumer_key = Utility::GetValueByName('woocommerce_consumer_key', $theme_name, getCurrentStore());
    
                config(['woocommerce.store_url' => $woocommerce_store_url]);
                config(['woocommerce.consumer_key' => $woocommerce_consumer_key]);
                config(['woocommerce.consumer_secret' => $woocommerce_consumer_secret]);
    
                $jsonData = Category::find($id);
    
                if (!$jsonData) {
                    return redirect()->back()->with('error', __('Category Not Found.'));
                }
    
                $woocommerceConectionQuery = WoocommerceConection::query();
                $woocommerceSubCat = (clone $woocommerceConectionQuery)->where('module', 'sub_category')->where('woocomerce_id', $jsonData['id'])->first();
    
                if (isset($jsonData['parent'])) {
                    $exist = (clone $woocommerceConectionQuery)->where('module', 'category')->where('woocomerce_id', $jsonData['parent'])->first();
                    if (!$exist) {
                        return redirect()->back()->with('error', __('This Sub Category Main Category Not Synced. Please First Create Sync Main Category.'));
                    }
                }
    
                if (!$woocommerceSubCat) {
                    return redirect()->back()->with('error', __('This Sub Category Main Category Not Synced. Please First Create Sync Main Category.'));
                }
    
                $store_id = Store::where('id', getCurrentStore())->first();
                if (!$store_id) {
                    return redirect()->back()->with('error', __('Store not found.'));
                }
    
                $subCategory = SubCategory::find($woocommerceSubCat->original_id);
                if (!$subCategory) {
                    return redirect()->back()->with('error', __('Subcategory not found.'));
                }
    
                if (!empty($jsonData['image']->src)) {
                    $url = $jsonData['image']->src;
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
                    $path = 'themes/' . APP_THEME() . '/uploads/';
                    $uplaod = Utility::upload_woo_file($url, $file2, $path);
                } else {
                    $url = asset(Storage::url('uploads/woocommerce.png'));
                    $name = 'woocommerce.png';
                    $file2 = rand(10, 100) . '_' . time() . "_" . $name;
                    $path = 'themes/' . APP_THEME() . '/uploads/';
                    $uplaod = Utility::upload_woo_file($url, $file2, $path);
                }
    
                if (!empty($uplaod)) {
                    $subCategory->name = $jsonData['name'];
                    $subCategory->image_url = $uplaod['full_url'];
                    $subCategory->image_path = $uplaod['url'];
                    $subCategory->icon_path = $uplaod['url'];
                    $subCategory->status = 1;
                    $subCategory->theme_id = APP_THEME();
                    $subCategory->store_id = getCurrentStore();
                    $subCategory->save();
    
                    return redirect()->back()->with('success', __('Category updated successfully.'));
                } else {
                    return redirect()->back()->with('error', __('Image upload failed.'));
                }
    
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
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
