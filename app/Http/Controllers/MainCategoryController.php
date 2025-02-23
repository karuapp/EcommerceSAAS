<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\MainCategory;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopifyConection;
use App\Models\WoocommerceConection;
use App\DataTables\MainCategoryDataTable;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;
class MainCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(MainCategoryDataTable $dataTable)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Manage Product Category'))
        {
            return  $dataTable->render('maincategory.index');
        }else{
            return redirect()->back()->with('error',__('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('maincategory.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(auth()->user() && auth()->user()->isAbleTo('Create Product Category'))
        {
            $store_id = Store::where('id', getCurrentStore())->first();

            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            $dir        = 'themes/'.APP_THEME().'/uploads';
            $totalImageSize = 0;
            if ($request->hasFile('image')) {
                $totalImageSize += $request->file('image')->getSize();
            }
            if ($request->hasFile('icon_image')) {
                $totalImageSize += $request->file('icon_image')->getSize();
            }
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $totalImageSize);
            if ($result != 1) {
                return redirect()->back()->with('error', $result);
            }
            if($request->image) {
                if ($result == 1)
                {
                    $fileName = rand(10,100).'_'.time() . "_" . $request->image->getClientOriginalName();
                    $path = Utility::upload_file($request,'image',$fileName,$dir,[]);
                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }
                else{
                    return redirect()->back()->with('error', $result);
                }
            }else{
                $path['full_url'] = asset(Storage::url('uploads/default.jpg'));
                $path['url'] = Storage::url('uploads/default.jpg');
            }

            if($request->icon_image) {
                if ($result == 1)
                {
                    $fileName = rand(10,100).'_'.time() . "_" . $request->icon_image->getClientOriginalName();
                    $paths = Utility::upload_file($request,'icon_image',$fileName,$dir,[]);
                    if ($paths['flag'] == 1) {
                        $url = $paths['url'];
                    } else {
                        return redirect()->back()->with('error', __($paths['msg']));
                    }
                }
                else{
                    return redirect()->back()->with('error', $result);
                }
            }else{
                $paths['url'] = Storage::url('uploads/default.jpg');
            }

            $MainCategory = new MainCategory();
            $MainCategory->name         = $request->name;
            $MainCategory->slug             =  'collections/' . strtolower(preg_replace("/[^\w]+/", "-", $request->name));
            $MainCategory->image_url    = $path['full_url'];
            $MainCategory->image_path   = $path['url'];
            $MainCategory->icon_path    = $paths['url'];
            $MainCategory->trending     = $request->trending;
            $MainCategory->status       = $request->status;
            $MainCategory->theme_id     = APP_THEME();
            $MainCategory->store_id     = getCurrentStore();

            $MainCategory->save();

            return redirect()->back()->with('success', __('Category successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MainCategory  $mainCategory
     * @return \Illuminate\Http\Response
     */
    public function show(MainCategory $mainCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MainCategory  $mainCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(MainCategory $mainCategory)
    {
        return view('maincategory.edit', compact('mainCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MainCategory  $mainCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MainCategory $mainCategory)
    {

        if(auth()->user() && auth()->user()->isAbleTo('Edit Product Category'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $dir        = 'themes/'.APP_THEME().'/uploads';

            $MainCategory = $mainCategory;
            $MainCategory->name = $request->name;

            $totalImageSize = 0;
            if ($request->hasFile('image')) {
                $totalImageSize += $request->file('image')->getSize();
            }
            if ($request->hasFile('icon_image')) {
                $totalImageSize += $request->file('icon_image')->getSize();
            }
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $totalImageSize);
            if ($result != 1) {
                return redirect()->back()->with('error', $result);
            }
            if(!empty($request->image)) {
                $file_path =  $mainCategory->image_path;
                
                if ($result == 1)
                {
                    if (!empty($file_path) && $file_path != '/storage/uploads/default.jpg' && \File::exists(base_path($file_path))) {
                        Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                    }

                    $fileName = rand(10,100).'_'.time() . "_" . $request->image->getClientOriginalName();
                    $path = Utility::upload_file($request,'image',$fileName,$dir,[]);
                    if ($path['flag'] == 1) {
                        $MainCategory->image_url    = $path['full_url'];
                        $MainCategory->image_path   = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }
                else{
                    return redirect()->back()->with('error', $result);
                }
            }else{
                $path['full_url'] = asset(Storage::url('uploads/default.jpg'));
                $path['url'] = Storage::url('uploads/default.jpg');
            }
            if (!empty($request->icon_image)) {
                $file_path = $mainCategory->icon_path;

                if ($result == 1) {
                    if (!empty($file_path) && $file_path != '/storage/uploads/default.jpg' && \File::exists(base_path($file_path))) {
                        Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                    }

                    $fileName = rand(10, 100) . '_' . time() . "_" . $request->icon_image->getClientOriginalName();
                    $paths = Utility::upload_file($request, 'icon_image', $fileName, $dir, []);
                    if ($paths['flag'] == 1) {
                        $mainCategory->icon_path = $paths['url'];
                    } else {
                        return redirect()->back()->with('error', __($paths['msg']));
                    }
                } else {
                    return redirect()->back()->with('error', $result);
                }
            }else{
                $paths['url'] = Storage::url('uploads/default.jpg');
            }

            $MainCategory->slug         =  'collections/' . strtolower(preg_replace("/[^\w]+/", "-", $request->name));
            $MainCategory->trending     = $request->trending;
            $MainCategory->status       = $request->status;
            $MainCategory->save();

            return redirect()->back()->with('success', __('Category successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MainCategory  $mainCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(MainCategory $mainCategory)
    {

        if(auth()->user() && auth()->user()->isAbleTo('Delete Product Category'))
        {
            $category = $mainCategory;

            if(!empty($category)) {

                MainCategory::mainCategoryImageDelete($category);

                $subCategories = $mainCategory->subCategoryDetail;
                foreach ($subCategories as $subCategory) {
                    SubCategory::subCategoryImageDelete($subCategory);
                }

                $products = $mainCategory->product_details;
                foreach ($products as $product) {
                    Product::productImageDelete($product);
                }

                WoocommerceConection::where('module', 'category')->where('original_id', $category->id)->delete();

                ShopifyConection::where('module', 'category')->where('original_id', $category->id)->delete();

                $category->delete();


            }
            return redirect()->back()->with('success', __('Category delete successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getProductCategories()
    {
        $store_id = Store::where('id', getCurrentStore())->first();
        $productCategory = MainCategory::where('theme_id',$store_id->theme_id)->where('store_id',getCurrentStore())->get();
        $html = '<div class="col-xxl-2 col-lg-3  col-sm-4 zoom-in ">
                    <div class="cat-active overflow-hidden" data-id="0">
                    <div class="category-select h-100" data-cat-id="0">
                        <button type="button" class="btn h-100 w-100 btn-primary btn-sm active pos-product-text">'.__("All Categories").'</button>
                    </div>
                    </div>
                </div>';
        foreach($productCategory as $key => $cat){
            $dcls = 'category-select';
            $html .= ' <div class="col-xxl-2 col-lg-3  col-sm-4 zoom-in cat-list-btn">
            <div class="overflow-hidden" data-id="'.$cat->id.'">
               <div class="h-100 '.$dcls.'" data-cat-id="'.$cat->id.'">
                  <button type="button" class="btn h-100 w-100 btn-primary btn-sm pos-product-text">'.$cat->name.'</button>
               </div>
            </div>
         </div>';

        }
        return Response($html);
    }
}
