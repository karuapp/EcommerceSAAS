<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MainCategory;
use App\Models\TaxOption;
use Illuminate\Support\Facades\Cache;
use DB;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'tag_id',
        'maincategory_id',
        'subcategory_id',
        'brand_id',
        'label_id',
        'tax_id',
        'tax_status',
        'shipping_id',
        'preview_type' ,
        'preview_video',
        'preview_content' ,
        'trending',
        'status',
        'video_url',
        'track_stock',
        'stock_order_status',
        'price',
        'sale_price',
        'product_stock',
        'minimum_quantity',
        'maximum_quantity',
        'low_stock_threshold',
        'downloadable_product',
        'product_weight',
        'cover_image_path' ,
        'cover_image_url' ,
        'stock_status',
        'variant_product',
        'attribute_id',
        'product_attribute',
        'custom_field_status',
        'custom_field',
        'description',
        'detail',
        'specification',
        'average_rating',
        'product_type',
        'theme_id',
        'store_id'
    ];


    protected $appends = ["category_name","sub_category_name","in_cart", "in_whishlist", "final_price"];


    public function getCategoryNameAttribute()
    {
        return !empty($this->ProductData) ? $this->ProductData->name : '';
    }

    public function getSubCategoryNameAttribute()
    {
        return !empty($this->SubCategoryctData) ? $this->SubCategoryctData->name : '';
    }

    public static function slugs($data)
    {
        $slug = '';
        // Remove special characters
        $slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $data);
        // Replace multiple spaces with a single hyphen
        $slug = preg_replace('/\s+/', '-', trim($slug));
        // Convert to lowercase
        $slug = strtolower($slug);
        $table = with(new Product)->getTable();

        $allSlugs = self::getRelatedSlugs($table, $slug ,$id = 0);

        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }
        for ($i = 1; $i <= 100; $i++) {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                return $newSlug;

            }
        }
    }

    protected static function getRelatedSlugs($table, $slug, $id = 0)
    {
        return DB::table($table)->select()->where('slug', 'like', $slug . '%')->where('id', '<>', $id)->get();
    }

    public function ProductData()
    {
        return $this->hasOne(MainCategory::class, 'id', 'maincategory_id');
    }

    public function brand()
    {
        return $this->hasOne(ProductBrand::class, 'id', 'brand_id');
    }

    public function label() {
        return $this->hasOne(ProductLabel::class, 'id', 'label_id');
    }

    public function reviewData()
    {
        return $this->hasMany(Testimonial::class, 'id', 'product_id');
    }

    public function tagData()
    {
        if($this->tag_id) {
            $tagIds = explode(',', $this->tag_id);
            $tags = Tag::whereIn('id', $tagIds)->select('id', 'name')->get()->toArray();
            return $tags;
        }
        return [];
    }


    public function SubCategoryctData()
    {
        return $this->hasOne(SubCategory::class, 'id', 'subcategory_id');
    }
    public function ProductVariant($sku_name = null)
    {
        $ProductStock = ProductVariant::where('product_id', $this->id)->where('variant', $sku_name)->first();
        return $ProductStock;
    }

    public static function bestseller_guest($theme_id = null, $storeId = null, $per_page = '6', $destination = 'app')
    {
        $bestseller_array_query = Product::where('theme_id', $theme_id)->where('store_id', $storeId)->where('status' , 1);
        if (!empty($destination) && $destination == 'web') {
            if ($per_page != 'all') {
                $bestseller_array_query->limit($per_page);
            }
            $bestseller_array = $bestseller_array_query->inRandomOrder()->get();
        } else {
            $bestseller_array = $bestseller_array_query->paginate($per_page);
        }
        // $bestseller_array = Product::where('theme_id', $theme_id)->where('tag_api', 'best seller')->paginate(6);
        $cart = 0;

        $return['status'] = 'success';
        $return['bestseller_array'] = $bestseller_array;
        $return['cart'] = $cart;
        return $return;
    }

    public static function Sub_image($product_id = 0)
    {
        $return['status'] = false;
        $return['data'] = [];
        $ProductImage = Cache::remember('product_image_' . $product_id, 3600, function () use ($product_id) {
            return ProductImage::where('product_id', $product_id)->get();
        });
        if ($ProductImage->isNotEmpty()) {
            $return['status'] = true;
            $return['data'] = $ProductImage;
        } else {
            $ProductImage = Product::select('id', 'store_id', 'theme_id', 'id as product_id', 'cover_image_path as image_path')->where('id', $product_id)->get();
            $return['status'] = true;
            $return['data'] = $ProductImage;
        }
        return $return;
    }

    public function getInWhishlistAttribute()
    {
        $id = !empty(auth('customers')->user()) ? auth('customers')->user()->id : 0;
        if (empty($id)) {
            $id = auth()->user() ? auth()->user()->id : 0;
        }
        $wishlist = Cache::remember("wishlist_{$this->id}_{$id}", 3600, function () use ($id) {
            return Wishlist::where('product_id', $this->id)->where('customer_id', $id)->exists();
        });
        return $wishlist;
    }

    public function getInCartAttribute()
    {
        $id = !empty(auth('customers')->user()) ? auth('customers')->user()->id : 0;
        return Cart::where('product_id', $this->id)->where('customer_id', $id)->exists();
    }


    public static function productSalesPage($theme, $slug, $productId, $details = false)
    {
        $storeId = getCurrenctStoreId($slug);
        $currentTheme = $theme;
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = \Carbon\Carbon::now();
        $sale_product = Cache::remember("flash_sale_{$theme}_{$storeId}", 3600, function () use ($theme, $storeId) {
            return FlashSale::where('theme_id', $theme)
            ->where('store_id', $storeId)
            ->where('is_active', 1)
            ->get();
        });

        $latestSales = [];

        if(module_is_active('PreOrder')){
            $customer = auth('customers')->user() ?? null;
            $pre_order_detail = \Workdo\PreOrder\app\Models\PreOrder::where('theme_id', $theme)->where('store_id', $storeId)->first();
            $product = Cache::remember('product_' . $productId, 3600, function () use ($productId) {
                return Product::find($productId);
            });
            if (isset($customer) && isset($product) && isset($pre_order_detail) && $pre_order_detail->enable_pre_order == 'on' && (($product->track_stock == 0 && $product->stock_status == 'out_of_stock') || ($product->variant_product == 0 && $product->product_stock <= 0) || ($product->variant_product == 1))) {
            }else{
                foreach ($sale_product as $flashsale) {
                    $saleEnableArray = json_decode($flashsale->sale_product, true);
                    $startDate = \Carbon\Carbon::parse($flashsale->start_date . ' ' . $flashsale->start_time);
                    $endDate = \Carbon\Carbon::parse($flashsale->end_date . ' ' . $flashsale->end_time);

                    if ($endDate < $startDate) {
                        $endDate->addDay();
                    }
                    $currentDateTime->setTimezone($startDate->getTimezone());

                    if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
                        if (is_array($saleEnableArray) && in_array($productId, $saleEnableArray)) {
                            $latestSales[$productId] = [
                                'discount_type' => $flashsale->discount_type,
                                'discount_amount' => $flashsale->discount_amount,
                                'start_date' => $flashsale->start_date,
                                'end_date' => $flashsale->end_date,
                                'start_time' => $flashsale->start_time,
                                'end_time' => $flashsale->end_time,
                            ];
                        }
                    }
                }
            }
        }else{
            foreach ($sale_product as $flashsale) {
                $saleEnableArray = json_decode($flashsale->sale_product, true);
                $startDate = \Carbon\Carbon::parse($flashsale->start_date . ' ' . $flashsale->start_time);
                $endDate = \Carbon\Carbon::parse($flashsale->end_date . ' ' . $flashsale->end_time);
    
                if ($endDate < $startDate) {
                    $endDate->addDay();
                }
                $currentDateTime->setTimezone($startDate->getTimezone());
    
                if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
                    if (is_array($saleEnableArray) && in_array($productId, $saleEnableArray)) {
                        $latestSales[$productId] = [
                            'discount_type' => $flashsale->discount_type,
                            'discount_amount' => $flashsale->discount_amount,
                            'start_date' => $flashsale->start_date,
                            'end_date' => $flashsale->end_date,
                            'start_time' => $flashsale->start_time,
                            'end_time' => $flashsale->end_time,
                        ];
                    }
                }
            }
        }

        if ($details) {
            return Cache::remember('product_detail_sale_lable_' . $currentTheme.'_'.$slug, 3600, function () use ($latestSales, $currentTheme) {
                return view('front_end.sections.product_detail_sale_lable', compact('latestSales','currentTheme'))->render();
            });
        }
        // return Cache::remember('product_sales' . $currentTheme.'_'.$slug, 3600, function () use ($latestSales, $currentTheme, $current_productId) {
            // return view('front_end.sections.product_sales', compact('latestSales','currentTheme'))->render();
        // });
        return view('front_end.sections.product_sales', compact('latestSales','currentTheme'))->render();
    }

    public static function productSalesTag($theme, $slug, $productId)
    {
        $storeId = getCurrenctStoreId($slug);
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = \Carbon\Carbon::now();
        $sale_product = Cache::remember("flash_sale_{$theme}_{$storeId}", 3600, function () use ($theme, $storeId) {
            return FlashSale::where('theme_id', $theme)
            ->where('store_id', $storeId)
            ->where('is_active', 1)
            ->get();
        });
        $latestSales = [];

        foreach ($sale_product as $flashsale) {
            $saleEnableArray = json_decode($flashsale->sale_product, true);
            $startDate = \Carbon\Carbon::parse($flashsale->start_date . ' ' . $flashsale->start_time);
            $endDate = \Carbon\Carbon::parse($flashsale->end_date . ' ' . $flashsale->end_time);

            if ($endDate < $startDate) {
                $endDate->addDay();
            }
            $currentDateTime->setTimezone($startDate->getTimezone());

            if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
                if (is_array($saleEnableArray) && in_array($productId, $saleEnableArray)) {
                    $latestSales[$productId] = [
                        'discount_type' => $flashsale->discount_type,
                        'discount_amount' => $flashsale->discount_amount,
                        'start_date' => $flashsale->start_date,
                        'end_date' => $flashsale->end_date,
                        'start_time' => $flashsale->start_time,
                        'end_time' => $flashsale->end_time,
                    ];
                }
            }
        }

       return $latestSales;
    }

    public static function ProductPrice($theme, $slug, $productId,$variantId = 0,$price=0)
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        $storeId = getCurrenctStoreId($slug);
        $product = Cache::remember('product_' . $productId, 3600, function () use ($productId) {
            return Product::find($productId);
        });
        if(empty($price))
        {
            if (!empty($product->sale_price)) {
                $price = $product->sale_price;
            } else {
                $price = $product->price;                
            }
        }
        // $price = $product->sale_price;
        $tax = Cache::remember('taxes_' . $product->tax_id, 3600, function () use ($product) {
            return Tax::find($product->tax_id);
        });

        $taxmethod = Cache::remember('tax_option_' . $product->tax_id.'_'.$theme.'_'.$storeId, 3600, function () use ($product, $theme, $storeId) {
            return TaxMethod::where('tax_id',$product->tax_id)->where('theme_id', $theme)->where('store_id', $storeId)->orderBy('priority', 'asc')->first();
        });
        $tax_option = Cache::remember('tax_option_' . $store->slug.'_'.$store->theme_id, 3600, function () use ($store) {
            return TaxOption::where('store_id', $store->id)
            ->where('theme_id', $store->theme_id)
            ->pluck('value', 'name')->toArray();
        });
        
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = \Carbon\Carbon::now()->toDateTimeString();
        $sale_product = Cache::remember("flash_sale_{$store->theme_id}_{$store->id}", 3600, function () use ($store) {
            return FlashSale::where('theme_id', $store->theme_id)
            ->where('store_id', $store->id)
            ->where('is_active', 1)
            ->get();
        });

        $latestSales = [];
        if(module_is_active('PreOrder')){
            $customer = auth('customers')->user() ?? null;
            $pre_order_detail = \Workdo\PreOrder\app\Models\PreOrder::where('theme_id', $store->theme_id)->where('store_id', $store->id)->first();
            if (isset($customer) && isset($product) && isset($pre_order_detail) && $pre_order_detail->enable_pre_order == 'on' && (($product->track_stock == 0 && $product->stock_status == 'out_of_stock') || ($product->variant_product == 0 && $product->product_stock <= 0) || ($product->variant_product == 1))) {
            }else{
                foreach ($sale_product as $flashsale) {
                    $saleEnableArray = json_decode($flashsale->sale_product, true);
                    $startDate = \Carbon\Carbon::parse($flashsale['start_date'] . ' ' . $flashsale['start_time']);
                    $endDate = \Carbon\Carbon::parse($flashsale['end_date'] . ' ' . $flashsale['end_time']);
        
                    if ($endDate < $startDate) {
                        $endDate->addDay();
                    }
                    if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
                        if (is_array($saleEnableArray) && in_array($productId, $saleEnableArray)) {
                            $latestSales[$productId] = [
                                'discount_type' => $flashsale->discount_type,
                                'discount_amount' => $flashsale->discount_amount,
                            ];
                        }
                    }
                }

            }
        }else{
            foreach ($sale_product as $flashsale) {
                $saleEnableArray = json_decode($flashsale->sale_product, true);
                $startDate = \Carbon\Carbon::parse($flashsale['start_date'] . ' ' . $flashsale['start_time']);
                $endDate = \Carbon\Carbon::parse($flashsale['end_date'] . ' ' . $flashsale['end_time']);
    
                if ($endDate < $startDate) {
                    $endDate->addDay();
                }
                if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
                    if (is_array($saleEnableArray) && in_array($productId, $saleEnableArray)) {
                        $latestSales[$productId] = [
                            'discount_type' => $flashsale->discount_type,
                            'discount_amount' => $flashsale->discount_amount,
                        ];
                    }
                }
            }
        }
        if ($latestSales == null) {
            $latestSales[$productId] = [
                'discount_type' => null,
                'discount_amount' => 0,
            ];
        }
        foreach ($latestSales as $productId => $saleData) {

            if ($product->variant_product == 0) {
                if ($saleData['discount_type'] == 'flat') {
                    $price = $product->sale_price - $saleData['discount_amount'];
                }
                if ($saleData['discount_type'] == 'percentage') {
                    $discount_price =  $product->sale_price * $saleData['discount_amount'] / 100;
                    $price = $product->sale_price - $discount_price;
                }
            } else {
                $product_variant_data = ProductVariant::where('product_id', $product->id)->where('id',$variantId)->first();

                if ($product_variant_data) {
                    if ($saleData['discount_type'] == 'flat') {
                        $price = $product_variant_data->price - $saleData['discount_amount'];
                    } elseif ($saleData['discount_type'] == 'percentage') {
                        $discount_price = $product_variant_data->price * $saleData['discount_amount'] / 100;
                        $price = $product_variant_data->price - $discount_price;
                    }else{
                        $price = $product_variant_data->price;
                    }
                }
            }
        }
        $price = max($price, 0);
        if($tax && count($tax->tax_methods()) > 0)
        {
            if(isset($tax_option['price_type']) && isset($tax_option['shop_price']) &&$tax_option['price_type'] == 'inclusive' && $tax_option['shop_price'] == 'including')
            {
            $tax_price = 0;
                if($product->variant_product == 0)
                {
                    // $tax_price = $taxmethod->tax_rate * $price / 100;
                    foreach ($tax->tax_methods() as $mkey => $method) {
                    $amount = $method->tax_rate * $price / 100;
                    $tax_price += $amount;
                    }
                    if($tax_option['round_tax'] == 1)
                    {
                        $include_price = $price + $tax_price;
                        $price = round($include_price);
                    }
                    else{
                        $price = $price + $tax_price;
                    }
                }else{
                    $variant_data = ProductVariant::where('id', $variantId)->first();
                    if($variant_data)
                    {
                        // $tax_price = $taxmethod->tax_rate * $variant_data->price / 100;
                        foreach ($tax->tax_methods() as $mkey => $method) {
                    	 $amount = $method->tax_rate * $variant_data->price / 100;
                    	 $tax_price += $amount;
                    	 }
                        if($tax_option['round_tax'] == 1)
                        {
                            $include_price = $variant_data->price + $tax_price;
                            $price = round($include_price);
                        }
                        else{
                            $price = $variant_data->price + $tax_price;
                        }
                    }
                }

            }else{
                if($product->variant_product == 0)
                {
                    $price = $price ;
                }else{
                    $variant_data = ProductVariant::where('id', $variantId)->first();
                    $price = $variant_data->price ?? ($price ?? 0);
                }
            }
        }else{
            $price = $price;
        }
        return $price;
    }

    public static function GetLatestProduct($theme, $slug = null, $no = 2)
    {
        $storeId = getCurrenctStoreId($slug);
        $currentTheme = $theme;
        $lat_products = Product::orderBy('created_at', 'Desc')->where('theme_id', $theme)->where('store_id', $storeId)->where('status' , 1)->limit($no)->get();
        return view('front_end.sections.homepage_latest_product', compact('currentTheme','theme','slug', 'lat_products'))->render();
    }

    public static function GetLatProduct($theme, $slug = null, $no = 1)
    {
        $storeId = getCurrenctStoreId($slug);
        $currentTheme = $theme;
        $latest_pro = Product::orderBy('created_at', 'Desc')->where('theme_id', $theme)->where('store_id', $storeId)->where('status' , 1)->limit($no)->first();
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        return view('front_end.sections.home_latest_product', compact('currentTheme','theme', 'storeId','slug', 'latest_pro','store'))->render();
    }

    public static function ProductPageBestseller($theme, $slug = null)
    {
        $currentTheme = $theme;
        $storeId = getCurrenctStoreId($slug);
        $MainCategory = MainCategory::where('theme_id', $theme)->where('store_id', $storeId)->get()->pluck('name', 'id');
        $MainCategory->prepend('All Products', '0');
        $homeproducts = Product::where('theme_id', $theme)->where('store_id', $storeId)->where('status' , 1)->get();
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        return view('front_end.sections.bestseller_product', compact('theme','homeproducts', 'MainCategory', 'slug', 'storeId','store', 'currentTheme'))->render();
    }

    // Calculate Product Inclusive amount
    public static function productTaxIncludeAmount($theme = null, $slug = null, $amount = 0, $taxId = null)
    {
        $storeId = getCurrenctStoreId($slug);
        $tax_price = 0;
        $tax_option = TaxOption::where('store_id',$storeId)
        ->where('theme_id',$theme)
        ->pluck('value', 'name')->toArray();
        if ($tax_option && $tax_option['price_type'] == 'inclusive') {
            $tax_price = Cart::getProductTaxAmount($taxId, $amount, $storeId, $theme, null, null, null, true);
        }

        return $amount + $tax_price;
    }

    public function getOriginalPriceAttribute()
    {
        $variantId = $this->getAttribute('variantId');
        $variantName = $this->getAttribute('variantName');
        $variant_data = ProductVariant::where('variant', $variantName)->where('product_id', $this->id)->first();

        $variant_id = !empty($variantId) ? $variantId : ($variant_data ? $variant_data->id : null);
        $price = $this->price;
        if ($this->variant_product == 1) {
            $ProductStock = ProductVariant::find($variant_id);
            $price = 0;
            if (!empty($ProductStock)) {
                if ($ProductStock->price == 0 && $ProductStock->variation_price == 0) {
                    $price = $this->price;
                } else {
                    $price = $ProductStock->variation_price;
                }
            }
        }
        return SetNumber($price);
    }

    public function getFinalPriceAttribute()
    {
        $variantId = $this->getAttribute('variantId');
        $variantName = $this->getAttribute('variantName');
        $variant_data = ProductVariant::where('variant', $variantName)->where('product_id', $this->id)->first();

        $variant_id = !empty($variantId) ? $variantId : ($variant_data ? $variant_data->id : null);
        $price = $this->price;
        $discount_type = $this->discount_type;
        $discount_amount = $this->discount_amount;
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = \Carbon\Carbon::now()->toDateTimeString();
        $sale_product = \App\Models\FlashSale::where('theme_id', APP_THEME())
            ->where('store_id', getCurrentStore())
            ->get();
        $latestSales = [];
        foreach ($sale_product as $flashsale) {
            if($flashsale->is_active == 1)
            {
                $saleEnableArray = json_decode($flashsale->sale_product, true);
                $startDate = \Carbon\Carbon::parse($flashsale['start_date'] . ' ' . $flashsale['start_time']);
                $endDate = \Carbon\Carbon::parse($flashsale['end_date'] . ' ' . $flashsale['end_time']);

                if ($endDate < $startDate) {
                    $endDate->addDay();
                }

                if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
                    if (is_array($saleEnableArray) && in_array($this->id, $saleEnableArray)) {
                        $latestSales[$this->id] = [
                            'discount_type' => $flashsale->discount_type,
                            'discount_amount' => $flashsale->discount_amount,
                        ];
                    }
                }
            }
        }
        if ($latestSales == null) {
            $latestSales[$this->id] = [
                'discount_type' => $this->discount_type,
                'discount_amount' => $this->discount_amount,
            ];
        }
        foreach ($latestSales as $productId => $saleData) {

            if ($this->variant_product == 0) {
                if ($saleData['discount_type'] == 'flat') {
                    $price = $this->price - $saleData['discount_amount'];
                }
                if ($saleData['discount_type'] == 'percentage') {
                    $discount_price =  $this->price * $saleData['discount_amount'] / 100;
                    $price = $this->price - $discount_price;
                }
            } else {
                $product_variant_data = ProductVariant::where('product_id', $this->id)->where('id',$variant_id)->first();

                if ($product_variant_data) {
                    if ($saleData['discount_type'] == 'flat') {
                        $price = $product_variant_data->price - $saleData['discount_amount'];
                    } elseif ($saleData['discount_type'] == 'percentage') {
                        $discount_price = $product_variant_data->price * $saleData['discount_amount'] / 100;
                        $price = $product_variant_data->price - $discount_price;
                    }else{
                        $price = $product_variant_data->price;
                    }
                }
            }
        }
        return SetNumber($price);
    }

    public static function instruction_array($theme_id = null, $store_id = null)
    {
        $return = [];
        if (!empty($theme_id)) {
            $path = base_path('themes/' . $theme_id . '/theme_json/homepage.json');
            $json = json_decode(file_get_contents($path), true);
            $setting_json = AppSetting::select('theme_json')
                ->where('theme_id', $theme_id)
                ->where('page_name', 'main')
                ->where('store_id', $store_id)
                ->first();
            if (!empty($setting_json)) {
                $json = json_decode($setting_json->theme_json, true);
            }
            foreach ($json as $key => $value) {
                if ($value['unique_section_slug'] == 'homepage-plant-instruction') {
                    if ($value['array_type'] == 'multi-inner-list') {
                        for ($i = 0; $i < $value['loop_number']; $i++) {
                            foreach ($value['inner-list'] as $key1 => $value1) {
                                // $img_path = '';
                                // $description = '';
                                if ($value1['field_slug'] == 'homepage-plant-instruction-image') {
                                    $img_path = $value1['field_default_text'];
                                    if (!empty($json[$key][$value1['field_slug']])) {
                                        if (!empty($json[$key][$value1['field_slug']][$i]['image'])) {
                                            $img_path = $json[$key][$value1['field_slug']][$i]['image'];
                                        }
                                    }
                                }
                                if ($value1['field_slug'] == 'homepage-plant-instruction-description') {
                                    $description = $value1['field_default_text'];
                                    $return[$i]['description'] = $value1['field_default_text'];
                                    if (!empty($json[$key][$value1['field_slug']])) {
                                    }
                                }
                            }
                            $return[$i]['img_path'] = $img_path;
                            $return[$i]['description'] = $description;
                        }
                    }
                }
            }
        }
        return $return;
    }

    public static function VariantAttribute($id = 0)
    {
        $return = '';
        if ($id) {
            $ProductVariant = ProductAttribute::find($id);
            if (!empty($ProductVariant)) {
                $return = $ProductVariant;
            }
        }
        return $return;
    }

    public static function productImageDelete($product)
    {
        $ProductImages = ProductImage::where('product_id', $product->id)->get();

        $Product = Product::find($product->id);
        $file_path1 = [];
        foreach ($ProductImages as $key => $ProductImage) {
            $file_path1[] =  $ProductImage->image_path;
        }
        $file_paths2[] = $Product->cover_image_path;
        if (!empty($Product->downloadable_product)) {
            $file_paths2[] = $Product->downloadable_product;
        }
        if ($Product->preview_type == "Video File" && !empty($Product->preview_content) && \File::exists(base_path($Product->preview_content))) {
            $file_paths2[] = $Product->preview_content;
        }
        $file_path = array_merge($file_path1, $file_paths2);
        Utility::changeproductStorageLimit(\Auth::user()->creatorId(), $file_path, $file_path1);
        if (!empty($ProductImages)) {
            // image remove from product variant image
            foreach ($ProductImages as $key => $ProductImage) {
                if (\File::exists(base_path($ProductImage->image_path))) {
                    \File::delete(base_path($ProductImage->image_path));
                }
            }
        }

        ProductImage::where('product_id', $product->id)->delete();

        ProductVariant::where('product_id', $product->id)->delete();

        Cart::where('product_id', $product->id)->delete();

        $Product = Product::find($product->id);
        if (!empty($Product)) {
            // image remove from description json
            $description_json = $Product->other_description_api;
            if (!empty($description_json)) {
                $description_json = json_decode($Product->other_description_api, true);
                foreach ($description_json['product-other-description'] as $key => $value) {
                    if ($value['field_type'] == 'photo upload') {
                        if (\File::exists(base_path($value['value']))) {
                            \File::delete(base_path($value['value']));
                        }
                    }
                }
            }

        }
    }

    public static function actionLinks($currentTheme, $slug, $product)
    {
        return view('front_end.hooks.action_link', compact('product', 'currentTheme', 'slug'))->render();
    }

    public function Tax()
    {
        return $this->hasOne(Tax::class, 'id', 'tax_id');
    }
    public static function ProductcardButton($currentTheme, $slug, $product)
    {
        return view('front_end.hooks.card_button', compact('product', 'currentTheme', 'slug'))->render();
    }

    public static function getProductPrice ($product, $store, $currentTheme) {
        $slug = $store->slug;
        return view('front_end.hooks.product_price', compact('product', 'currentTheme', 'store','slug'))->render();
    }

    public static function ManageProductPrice ($item, $store, $currentTheme) {
        $slug = $store->slug;
        return view('front_end.hooks.manage_product_price', compact('item', 'currentTheme', 'store','slug'))->render();
    }

    public static function ManageCartPrice ($item, $store, $currentTheme) {
        $slug = $store->slug;
        return view('front_end.hooks.manage_cart_price', compact('item', 'currentTheme', 'store','slug'))->render();
    }

    public static function ManageCheckoutPrice ($item, $store, $currentTheme) {
        $slug = $store->slug;
        return view('front_end.hooks.manage_checkout_price', compact('item', 'currentTheme', 'store','slug'))->render();
    }

    public static function ManageCheckoutProductPrice ($item, $store, $currentTheme) {
        $slug = $store->slug;
        return view('front_end.hooks.manage_checkout_product_price', compact('item', 'currentTheme', 'store','slug'))->render();
    }

    public static function ManageCartListPrice ($item, $store, $currentTheme) {
        $slug = $store->slug;
        return view('front_end.hooks.manage_cartlist_price', compact('item', 'currentTheme', 'store','slug'))->render();
    }
}
