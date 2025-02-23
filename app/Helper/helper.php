<?php

use App\Models\Themes\{ThemeSection, ThemeBestProductSection, ThemeArticelBlogSection, ThemeArticelBlogSectionDraft, ThemeTopProductSection, ThemeTopProductSectionDraft, ThemeModernProductSection, ThemeModernProductSectionDraft, ThemeBestProductSectionDraft, ThemeBestProductSecondSection, ThemeBestProductSecondSectionDraft, ThemeBestSellingSection, ThemeBestSellingSectionDraft, ThemeLogoSliderSection, ThemeLogoSliderSectionDraft, ThemeBestsellerSliderSection, ThemeBestsellerSliderSectionDraft, ThemeBestsellerSection, ThemeBestsellerSectionDraft, ThemeHeaderSection, ThemeSliderSection, ThemeCategorySection, ThemeReviewSection, ThemeSectionDraft, ThemeHeaderSectionDraft, ThemeSliderSectionDraft, ThemeCategorySectionDraft, ThemeReviewSectionDraft, ThemeSectionMap, ThemeBlogSection, ThemeBlogSectionDraft, ThemeDiscountSection, ThemeDiscountSectionDraft, ThemeProductCategorySection, ThemeProductCategorySectionDraft, ThemeFooterSection, ThemeFooterSectionDraft, ThemeProductSection, ThemeProductSectionDraft, ThemeSubscribeSection, ThemeSubscribeSectionDraft, ThemeVariantBackgroundSection, ThemeVariantBackgroundSectionDraft, ThemeProductBannerSliderSection, ThemeProductBannerSliderSectionDraft, ThemeNewestCateorySectionDraft, ThemeNewestCateorySection, ThemeServiceSection, ThemeServiceSectionDraft, ThemeVideoSection, ThemeVideoSectionDraft, ThemeNewestProductSection, ThemeNewestProductSectionDraft};
use App\Models\ProductAttributeOption;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\Page;
use App\Models\{MainCategory, SubCategory};
use App\Models\Setting;
use App\Models\User;
use App\Models\TaxOption;
use App\Models\Store;
use Illuminate\Support\Facades\Artisan;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Utility;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\{Currency, PixelFields};
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Addon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Hashids\Hashids;
use App\Facades\ModuleFacade as Module;
use App\Models\userActiveModule;
use Illuminate\Support\Facades\Session;
use App\Models\AddOnManager;
use App\Models\AppSetting;
use App\Models\Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Workdo\SidebarCustomization\app\Models\SideMenuOption;
use Workdo\SidebarCustomization\app\Models\SideMenuOrder;

if (!function_exists('module_path')) {
    function module_path($name, $path = '')
    {
        return base_path('packages/Workdo/' . $name . '/' . $path);
    }
}

if (!function_exists('getMenu')) {
    function getMenu()
    {
        $user = auth()->user();
        $role = $user->type ?? null;
        $menu = new \App\Classes\Menu($user);
        if ($role && $role == 'super admin') {
            event(new \App\Events\SuperAdminMenuEvent($menu));
        } else {
            event(new \App\Events\CompanyMenuEvent($menu));
        }
        return generateMenu($menu->menu, null);
    }
}

if (!function_exists('generateMenu')) {
    function generateMenu($menuItems, $parent = null)
    {
        $html = '';

        $filteredItems = array_filter($menuItems, function ($item) use ($parent) {
            return $item['parent'] == $parent;
        });
        usort($filteredItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        foreach ($filteredItems as $item) {
            if ($item['name'] == 'mobilescreensetting' && env('IS_MOBILE') != 'yes') {
                continue;
            }
            $hasChildren = hasChildren($menuItems, $item['name']);
            if ($item['parent'] == null) {
                $html .= '<li class="dash-item dash-hasmenu">';
            } else {
                $html .= '<li class="dash-item">';
            }
            $html .= '<a href="' . (!empty($item['route']) ? route($item['route']) : '#!') . '" class="dash-link">';

            if ($item['parent'] == null) {
                $html .= ' <span class="dash-micon"><i class="ti ti-' . $item['icon'] . '"></i></span>
                <span class="dash-mtext">';
            }
            $html .= __($item['title']) . '</span>';
            if ($hasChildren) {
                $html .= '<span class="dash-arrow"> <i data-feather="chevron-right"></i> </span> </a>';
                $html .= '<ul class="dash-submenu">';
                $html .= generateMenu($menuItems, $item['name']);
                $html .= '</ul>';
            } else {
                $html .= '</a>';
            }
            $html .= '</li>';
        }
        return $html;
    }
}

if (!function_exists('hasChildren')) {
    function hasChildren($menuItems, $name)
    {
        foreach ($menuItems as $item) {
            if ($item['parent'] === $name) {
                return true;
            }
        }
        return false;
    }
}


if (!function_exists('SetNumberFormat')) {
    function SetNumberFormat($number = 0)
    {
        $settings = getAdminAllSetting();
        $currency = $settings['defult_currancy_symbol'] ?? ($settings['CURRENCY'] ?? '$');
        $number_output = number_format($number, 2);
        return $currency . str_replace(',', '', $number_output);
    }
}

if (!function_exists('SetNumber')) {
    function SetNumber($number = 0)
    {
        if (is_string($number)) {
            preg_match('/\d+/', $number, $matches);
            $number = (float) $matches[0];
        }
        $number_output = number_format($number, 2);
        return str_replace(',', '', $number_output);
    }
}

if (!function_exists('get_file')) {
    function get_file($path, $theme_id = '')
    {
        $admin = Cache::remember('super_admin_details', 3600, function () {
            return User::where('type', 'super admin')->first();
        });
        if (empty($theme_id)) {
            $theme_id = APP_THEME() ?? 'grocery';
        }

        $settings = Cache::rememberForever('default_getfile_' . $theme_id . '_' . $admin->current_store, function () use ($theme_id, $admin) {
            return Setting::where('theme_id', $theme_id)->where('store_id', $admin->current_store)->pluck('value', 'name')->toArray();
        });

        if (empty($path)) {
            return $path;
        }

        try {
            if (isset($settings) && (count($settings) > 0) && $settings['storage_setting'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com'
                    ]
                );
                return \Storage::disk($settings['storage_setting'])->url($path);
            } elseif (isset($settings) && (count($settings) > 0) && $settings['storage_setting'] == 's3') {
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['s3_key'],
                        'filesystems.disks.s3.secret' => $settings['s3_secret'],
                        'filesystems.disks.s3.region' => $settings['s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
                return \Storage::disk($settings['storage_setting'])->url($path);
            } else {
                $path = url($path);
                return $path;
            }
        } catch (\Throwable $th) {
            return '';
        }
    }
}

if (!function_exists('getEnableSetting')) {
    function getEnableSetting($remote)
    {
        $settingQuery = Setting::query();
        $subdomain = (clone $settingQuery)->where('name', 'subdomain')->where('value', $remote)->first();
        $domain = (clone $settingQuery)->where('name', 'domains')->where('value', $remote)->first();

        return $subdomain ? $subdomain : $domain;
    }
}

if (!function_exists('isEnabled')) {
    function isEnabled($enableSetting)
    {
        $admin = User::find($enableSetting->created_by);

        return $enableSetting->value == 'on' && $enableSetting->store_id == $admin->current_store;
    }
}

if (!function_exists('getStoreIdFromUser')) {
    function getStoreIdFromUser($userId)
    {
        $user = User::find($userId);

        return $user ? $user->current_store : 1;
    }
}

if (!function_exists('getStoreIdFromSlugOrUser')) {
    function getStoreIdFromSlugOrUser($slug, $userId)
    {
        if ($slug) {
            return Store::where('slug', $slug)->value('id');
        }

        $userId = $userId ?? auth()->user()->id ?? 1;
        $user = User::find($userId);

        if (!$user) {
            return 1;
        }

        if ($user->type != 'admin' && $user->type != 'super admin') {
            $user = User::find($user->created_by);
        }

        return $user->current_store ?? Store::where('created_by', $user->id)->value('id') ?? 1;
    }
}

// setConfigEmail ( SMTP )
if (!function_exists('SetConfigEmail')) {
    function SetConfigEmail($request)
    {
        try {
            config(
                [
                    'mail.driver' => $request->mail_driver,
                    'mail.host' => $request->mail_host,
                    'mail.port' => $request->mail_port,
                    'mail.encryption' => $request->mail_encryption,
                    'mail.username' => $request->mail_username,
                    'mail.password' => $request->mail_password,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name,
                ]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('upload_theme_image')) {
    function upload_theme_image($theme_name, $theme_image, $key = 0)
    {
        $return['status'] = false;
        $return['image_url'] = '';
        $return['image_path'] = '';
        $return['message'] = __('Something went wrong.');

        if (!empty($theme_image)) {
            $theme_image = $theme_image;
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $theme_image->getSize());
            if ($result == 1) {
                $filenameWithExt = $theme_image->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $theme_image->getClientOriginalExtension();
                $filedownloadable1 = $key . rand(0, 100) . date('Ymd') . '_' . time() . '.' . $extension;
                $dir = 'themes/' . $theme_name . '/uploads';
                $save = Storage::disk('theme')->putFileAs(
                    $dir,  // upload path
                    $theme_image, // image name
                    $filedownloadable1  // image new name
                );
                $return['status'] = true;
                $return['image_url'] = url('themes/' . $save);
                $return['image_path'] = $save;
                $return['message'] = __('Image upload succcessfully.');
            } else {
                $return['status'] = false;
                $return['image_url'] = '';
                $return['image_path'] = '';
                $return['message'] = __('Plan storage limit is over so please upgrade the plan.');
            }
        }
        return $return;
    }
}

if (!function_exists('getSuperAdminAllSetting')) {
    function getSuperAdminAllSetting()
    {
        // Laravel cache
        // return Cache::rememberForever('admin_settings',function () {
        $super_admin = Cache::remember('super_admin_details', 3600, function () {
            return User::where('type', 'super admin')->first();
        });

        $settings = [];
        if ($super_admin) {
            $settings = Cache::remember("setting_superadmin_{$super_admin->id}", 3600, function () use ($super_admin) {
                return Setting::where('created_by', $super_admin->id)->where('store_id', $super_admin->current_store)->pluck('value', 'name')->toArray();
            });
        }
        return $settings;
        // });
    }
}

if (!function_exists('getAdminAllSetting')) {
    function getAdminAllSetting($user_id = null, $store_id = null, $theme_id = null)
    {
        if (!empty($user_id)) {
            $user = Cache::remember("user_{$user_id}", 3600, function () use ($user_id) {
                return User::find($user_id);
            });
        } else {
            $user =  auth()->user();
        }
        // Check if the user is not 'company' or 'super admin' and find the creator
        if (isset($user->type) && !in_array($user->type, ['admin', 'super admin'])) {
            $user = User::find($user->created_by);
        }
        if (!empty($user)) {
            $store_id = $store_id ?? $user->current_store;
            $store = Cache::remember('store_' . $store_id, 3600, function () use ($store_id) {
                return Store::find($store_id);
            });
            $theme_id = $theme_id ?? ($store->theme_id ?? 'grocery');

            // return Cache::rememberForever($key,function () use ($user, $store_id) {
            $settings = [];
            $settings =  Cache::remember('admin_setting_' . $store_id . '_' . $theme_id . '_' . $user->id, 3600, function () use ($store_id, $user, $theme_id) {
                return Setting::where('created_by', $user->id)->where('store_id', $store_id)->where('theme_id', $theme_id)->pluck('value', 'name')->toArray();
            });

            return $settings;
            // });
        } elseif (!empty($store_id) && !empty($theme_id)) {
            $settings = [];
            $settings = Setting::where('store_id', $store_id)->where('theme_id', $theme_id)->pluck('value', 'name')->toArray();
            return $settings;
        } else {
            $settings = [];
            $user = User::where('type', 'admin')->first();
            $settings = Setting::where('created_by', $user->id)->pluck('value', 'name')->toArray();
            return $settings;
        }
        return [];
    }
}

if (!function_exists('getCurrentStore')) {
    function getCurrentStore($user_id = null)
    {
        // Get the user
        if (!empty($user_id)) {
            $user = Cache::remember("user_{$user_id}", 3600, function () use ($user_id) {
                return User::find($user_id);
            });
        } else {
            $user = auth()->user();
        }

        if ($user) {
            // Check if user has a current store
            $storeId = $user->current_store;
            if (!empty($storeId)) {
                return $storeId;
            } else {
                // Handle super admin case
                if ($user->type == 'super admin') {
                    return 1; // Default store for super admin
                } else {
                    // Cache the store query result to reduce calls
                    return Cache::remember("store_current_" . $user->id, 3600, function () use ($user) {
                        $store = Store::where('created_by', $user->id)->first();
                        return $store ? $store->id : null;
                    });
                }
            }
        }
    }
}

if (!function_exists('APP_THEME')) {
    function APP_THEME($user_id = null)
    {
        // Get the user
        if (!empty($user_id)) {
            $user = Cache::remember("user_{$user_id}", 3600, function () use ($user_id) {
                return User::find($user_id);
            });
        } else {
            $user = auth()->user();
        }

        if ($user) {
            // Check if user has a current store
            $storeId = $user->current_store;

            if (!empty($storeId)) {
                // Cache the store's theme ID to reduce calls
                $store = Cache::remember('store_' . $storeId, 3600, function () use ($storeId) {
                    return Store::find($storeId);
                });

                if (!$store) {
                    if (auth()->check()) {
                        auth()->logout();
                    }

                    return redirect()->route('login')->with('message', 'You have been logged out.');
                }
                return $store->theme_id ?? null;
            } else {
                // Handle super admin case
                return 'grocery';
            }
        }
    }
}

if (!function_exists('currency')) {
    function currency($code = null)
    {
        if ($code == null) {
            $c = Currency::get();
        } else {
            $c = Currency::where('code', $code)->first();
        }
        return $c;
    }
}

if (!function_exists('getThemeProductsBasedOnType')) {
    function getThemeProductsBasedOnType($type, $productIds, $storeId, $themeId)
    {
        $productQuery = Product::where('store_id', $storeId)->where('theme_id', $themeId)->where('product_type', null)->where('status', 1);

        $all_products = (clone $productQuery)->inRandomOrder()->limit(20)->get();
        $modern_products = (clone $productQuery)->orderByDesc('created_at')->limit(6)->get();
        $home_products = (clone $productQuery)->orderBy('created_at')->limit(4)->get();
        $home_page_products = (clone $productQuery)->orderByDesc('created_at')->limit(2)->get();

        switch ($type) {
            case 'tranding_product':
                $products = (clone $productQuery)->where('trending', 1)->limit(20)->get();
                break;
            case 'latest_product':
                $products = (clone $productQuery)->orderByDesc('created_at')->limit(20)->get();
                break;
            case 'random_product':
                $products = (clone $productQuery)->inRandomOrder()->limit(20)->get();
                break;
            case 'custom_product':
                $products = (clone $productQuery)->whereIn('id', $productIds)->get();
                break;
            case 'category_wise_product':
                $products = (clone $productQuery)->whereIn('maincategory_id', $productIds)->get();
                break;
            case 'variant_product':
                $products = (clone $productQuery)->where('variant_product', 1)->limit(20)->get();
                break;
            case 'without_variant_product':
                $products = (clone $productQuery)->where('variant_product', 0)->limit(20)->get();
                break;
            default:
                $products = (clone $productQuery)->limit(20)->get();
                break;
        }

        return $products;
    }
}

if (!function_exists('getProductCategory')) {
    function getProductCategory($categoryId, $storeId, $themeId)
    {
        if (count($categoryId) > 0) {
            $categories = MainCategory::with('product_details')->where('status', 1)->whereIn('id', $categoryId)->where('store_id', $storeId)->where('theme_id', $themeId)->get();
        } else {
            $categories = MainCategory::with('product_details')->where('status', 1)->where('store_id', $storeId)->where('theme_id', $themeId)->get();
        }

        return $categories;
    }
}

if (!function_exists('GetCurrenctTheme')) {
    function GetCurrenctTheme($storeSlug)
    {

        if ($storeSlug == null) {
            $uri = url()->full();
            $segments = explode('/', str_replace('' . url('') . '', '', $uri));
            $segments = $segments[1] ?? null;

            $local = parse_url(config('app.url'))['host'];
            // Get the request host
            $remote = request()->getHost();
            // Get the remote domain
            // remove WWW
            $remote = str_replace('www.', '', $remote);
            $settingQuery = Setting::query();
            $subdomain = (clone $settingQuery)->where('name', 'subdomain')->where('value', $remote)->first();
            $domain = (clone $settingQuery)->where('name', 'domains')->where('value', $remote)->first();

            $enable_subdomain = "";
            $enable_domain = "";

            if ($subdomain || $domain) {
                if ($subdomain) {
                    $enable_subdomain = (clone $settingQuery)->where('name', 'enable_subdomain')->where('value', 'on')->where('store_id', $subdomain->store_id)->first();
                }

                if ($domain) {
                    $enable_domain = (clone $settingQuery)->where('name', 'enable_domain')->where('value', 'on')->where('store_id', $domain->store_id)->first();
                }
            }
            if ($enable_domain || $enable_subdomain) {
                $userQuery = User::query();
                if ($enable_subdomain) {
                    $admin = (clone $userQuery)->find($enable_subdomain->created_by);
                    if ($enable_subdomain->value == 'on' && $enable_subdomain->store_id == $admin->current_store) {
                        if (auth()->user() && getCurrentStore()) {
                            $store_id = getCurrentStore() ?? auth()->user()->current_store;
                        } else {
                            $store_id = $admin->current_store;
                        }
                    } else {
                        $store_id = $admin->current_store;
                    }
                } elseif ($enable_domain) {
                    $admin = (clone $userQuery)->find($enable_domain->created_by);
                    if ($enable_domain->value == 'on' && $enable_domain->store_id == $admin->current_store) {
                        if (auth()->user() && getCurrentStore()) {
                            $store_id = getCurrentStore() ?? auth()->user()->current_store;
                        } else {
                            $store_id = $admin->current_store;
                        }
                    } else {
                        $store_id = $admin->current_store;
                    }
                }
            }


            $store = Cache::remember('store_' . $store_id, 3600, function () use ($store_id) {
                return Store::where('id', $store_id)->first();
            });
            $theme_id = $store->theme_id;
        } else {
            $store = Cache::remember('store_' . $storeSlug, 3600, function () use ($storeSlug) {
                return Store::where('slug', $storeSlug)->first();
            });
            $theme_id = $store->theme_id ?? '';
        }
        return $theme_id;
    }
}

if (!function_exists('getCurrenctStoreId')) {
    function getCurrenctStoreId($storeSlug)
    {
        if ($storeSlug == null) {
            $store_id = getCurrentStore();
            $store = Cache::remember('store_' . getCurrentStore(), 3600, function () use ($store_id) {
                return Store::where('id', $store_id)->first();
            });
            $id = $store->id;
        } else {
            $store = Cache::remember('store_' . $storeSlug, 3600, function () use ($storeSlug) {
                return Store::where('slug', $storeSlug)->first();
            });
            $id = $store->id ?? '';
        }
        return $id;
    }
}

if (!function_exists('getThemeSections')) {
    function getThemeSections($currentTheme, $storeSlug, $is_publish, $isLandingPage = false)
    {
        // Load site-wide settings
        $SITE_RTL = null;
        $store = Cache::remember('store_' . $storeSlug, 3600, function () use ($storeSlug) {
            return Store::where('slug', $storeSlug)->first();
        });
        if (!$store) {
            abort(404);
        }

        // Cache key based on theme_id and store_id
        $cacheKey = "home_page_sections_{$currentTheme}_{$store->id}";

        // Attempt to retrieve data from cache
        $cachedData = Cache::get($cacheKey);
        // if ($cachedData) {
        //     return $cachedData;
        // }
        $theme_id = $currentTheme;
        $store_id = $store->id;
        $slug = $store->slug;
        $stringid = $store->id;
        themeDefaultSection($store->theme_id, $store->id);
        $setting = getAdminAllSetting(null, $store_id, $currentTheme);
        // $setting = Setting::where('theme_id', $currentTheme)->where('store_id', $store_id)->pluck('value', 'name')->toArray();

        $color = 'theme-3';
        $themeSectionQuery = ThemeSection::query();
        $themeSectionDraftQuery = ThemeSectionDraft::query();

        // Load theme sections
        if ($is_publish) {
            $theme_section = (clone $themeSectionQuery)
                ->where('theme_id', $currentTheme)
                ->where('store_id', $store_id);
            if ($isLandingPage) {
                $theme_section->where('is_hide', 0);
            }

            $theme_section = $theme_section->orderBy('order', 'asc')->get();
        } else {
            $theme_section = (clone $themeSectionDraftQuery)
                ->where('theme_id', $currentTheme)
                ->where('store_id', $store_id);
            if ($isLandingPage) {
                $theme_section->where('is_hide', 0);
            }

            $theme_section = $theme_section->orderBy('order', 'asc')->get();
            if (count($theme_section) == 0) {
                $theme_section = (clone $themeSectionQuery)
                    ->where('theme_id', $currentTheme)
                    ->where('store_id', $store_id);
                if ($isLandingPage) {
                    $theme_section->where('is_hide', 0);
                }

                $theme_section = $theme_section->orderBy('order', 'asc')->get();
            }
        }

        $jsonPaths = $theme_section->pluck('section_name')->toArray();
        // Load other JSON data

        $json_data = [];

        foreach ($jsonPaths as $jsonPath) {
            // Get Published or draft json file from database or root directory
            $data = arrayToObject(getThemeMainOrDraftSectionJson($is_publish, $currentTheme, $jsonPath, $store_id));

            $json_data[$jsonPath] = $data->json_data;
        }

        // Load discount products
        $productQuery = Product::where('theme_id', $store->theme_id)
            ->where('store_id', $store->id)->where('product_type', null)->where('status', 1);

        // Load currency and language information
        $currency_icon = $currency = Utility::GetValueByName('defult_currancy_symbol', $theme_id, $store_id);
        if (empty($currency)) {
            $currency_icon = $currency = '$';
        }
        $languages = Utility::languages();

        $currantLang = \Cookie::get('LANGUAGE') ?? $store->default_language;

        if (!empty($currantLang)) {
            \App::setLocale($currantLang);
        } elseif (empty($currantLang)) {
            \App::setLocale($store->default_language);
        }

        // Load theme logo
        $theme_logo = Utility::GetValueByName('theme_logo', $theme_id, $store->id);

        $theme_favicon = Utility::GetValueByName('theme_favicon', $theme_id, $store->id);
        $theme_favicons = $theme_favicon = get_file($theme_favicon, $theme_id);

        $theme_image = Utility::GetValueByName('theme_image', $theme_id, $store->id);
        $theme_image = $theme_image = get_file($theme_image, $theme_id);

        $category_options = Cache::remember('category_options_' . $store->id . '_' . $store->theme_id, 3600, function () use ($store) {
            return MainCategory::where('theme_id', $store->theme_id)
                ->where('store_id', $store->id)
                ->where('status', 1)
                ->pluck('name', 'id')
                ->prepend('All Products', '0');
        });

        $products = Cache::remember('products_' . $store->id . '_' . $store->theme_id, 3600, function () use ($productQuery) {
            return (clone $productQuery)->get();
        });

        $has_subcategory = Utility::themeSubcategory($store->theme_id, $store->id);
        $section = (object) $json_data;
        $common_page = getInnerPageJson($store->theme_id, $store->id, 'common_page');
        $section->common_page = (object) $common_page['common_page'];
        $topNavItems = [];
        $menu_id = [];
        if (isset($section->header) && isset($section->header->section) && isset($section->header->section->menu_type) && isset($section->header->section->menu_type->menu_ids)) {
            $menu_id = (array) $section->header->section->menu_type->menu_ids;
        }

        $metakeyword = Utility::GetValueByName('metakeyword', $theme_id, $store->id);
        $metadesc = Utility::GetValueByName('metadesc', $theme_id, $store->id);
        $metaimage = Utility::GetValueByName('metaimage', $theme_id, $store->id);
        $metaimage = get_file($metaimage, $theme_id, $store->id);

        $google_analytic = Utility::GetValueByName('google_analytic', $theme_id, $store->id);
        $storejs = Utility::GetValueByName('storejs', $theme_id, $store->id);
        $storecss = Utility::GetValueByName('storecss', $theme_id, $store->id);
        $fbpixel_code = Utility::GetValueByName('fbpixel_code', $theme_id, $store->id);

        $pixels = Cache::remember('pixels_' . $store->id . '_' . $store->theme_id, 3600, function () use ($store) {
            return  PixelFields::where('store_id', $store->id)
                ->where('theme_id', $store->theme_id)
                ->get();
        });
        $pixelScript = [];
        foreach ($pixels as $pixel) {
            if (isset($pixel['platform']) && isset($pixel['pixel_id']) && !empty($pixel['platform']) && !empty($pixel['pixel_id']))
                $pixelScript[] = pixelSourceCode($pixel['platform'], $pixel['pixel_id']);
        }
        $topNavItems = get_nav_menu($menu_id);
        $whatsapp_setting_enabled = Utility::GetValueByName('whatsapp_setting_enabled', $theme_id, $store_id);
        $whatsapp_setting_enabled = !empty($whatsapp_setting_enabled) && $whatsapp_setting_enabled == 'on' ? 1 : 0;
        $whatsapp_contact_number = Utility::GetValueByName('whatsapp_contact_number', $theme_id, $store_id);

        if (empty($whatsapp_contact_number)) {
            $whatsapp_contact_number = Utility::GetValueByName('whatsapp_number', $theme_id, $store_id);
        }
        $pages = Page::where('theme_id', $store->theme_id)->where('page_status', '1')->where('store_id', $store->id)->get();

        $tax_option = Cache::remember('tax_option_' . $store->slug, 3600, function () use ($store) {
            return TaxOption::where('store_id', $store->id)
                ->where('theme_id', $store->theme_id)
                ->pluck('value', 'name')->toArray();
        });
        $resultData = compact('currentTheme', 'pixelScript', 'whatsapp_setting_enabled', 'whatsapp_contact_number', 'theme_section', 'section', 'setting', 'theme_logo', 'currantLang', 'stringid', 'languages', 'currency', 'SITE_RTL', 'color', 'is_publish', 'slug', 'store', 'theme_id', 'category_options', 'store_id', 'topNavItems', 'products', 'tax_option', 'theme_favicon', 'theme_favicons', 'google_analytic', 'storejs', 'storecss', 'fbpixel_code', 'metaimage', 'metadesc', 'metakeyword', 'currency_icon', 'theme_image', 'pages');

        // Store the result in cache for future requests
        Cache::put($cacheKey, $resultData, 3600); // Cache for 1 hour

        return $resultData;
    }
}

if (!function_exists('mergeThemeJson')) {
    function mergeThemeJson($theme_json, $database_json)
    {
        if (is_array($theme_json) && count($theme_json) > 0) {
            $new_json = [];
            foreach ($theme_json as $key => $value) {
                if (array_key_exists($key, $database_json)) {
                    // Merge arrays with custom logic
                    $new_json[$key] = arrayToObject(array_replace_recursive(objectToArray($value), objectToArray($database_json[$key])));
                } else {
                    $new_json[$key] = $value;
                }
            }
            return $new_json;
        } else {
            return arrayToObject(array_replace_recursive(objectToArray($theme_json), objectToArray($database_json)));
        }
    }
}

// Convert the object to an array recursively
if (!function_exists('objectToArray')) {
    function objectToArray($obj)
    {
        if (is_object($obj) || is_array($obj)) {
            $arr = [];
            foreach ($obj as $key => $value) {
                $arr[$key] = objectToArray($value);
            }
            return $arr;
        } else {
            return $obj;
        }
    }
}

// Convert the array to an object recursively
if (!function_exists('arrayToObject')) {
    function arrayToObject($array)
    {
        if (is_array($array)) {
            $obj = new stdClass();
            foreach ($array as $key => $value) {
                $obj->$key = arrayToObject($value);
            }
            return $obj;
        } else {
            return $array;
        }
    }
}

if (!function_exists('getCategoryBasedProduct')) {
    function getCategoryBasedProduct($categoryId, $storeId, $themeId)
    {
        if ($categoryId == 0) {
            $products = Product::where('store_id', $storeId)->where('theme_id', $themeId)->where('product_type', null)->where('status', 1)->get();
        } else {
            $products = Product::where('maincategory_id', $categoryId)->where('store_id', $storeId)->where('theme_id', $themeId)->where('product_type', null)->where('status', 1)->get();
        }
        return $products;
    }
}

if (!function_exists('get_nav_menu')) {
    function get_nav_menu($productIds)
    {
        // Fetch the top navigation menu
        $topNav = is_array($productIds)
            ? Menu::whereIn('id', $productIds)->first()
            : Menu::where('id', $productIds)->first();

        if ($topNav) {
            $topNavItems = json_decode($topNav->content);
            if ($topNavItems) {

                if (is_object($topNavItems[0])) {
                    $topNavItems[0] = array_values((array)$topNavItems[0]);
                }

                // Remove duplicate items by 'id'
                $topNavItems[0] = array_values(array_reduce($topNavItems[0], function ($carry, $item) {
                    if (!in_array($item->id, array_column($carry, 'id'))) {
                        $carry[] = $item; // Add the item if its id is not in the carry array
                    }
                    return $carry;
                }, []));

                // Extract all menu item IDs
                $menuItemIds = array_merge(
                    [$topNavItems[0][0]->id], // Add the first top-level item ID
                    array_column($topNavItems[0], 'id'), // Add all top-level menu IDs
                    ...array_map(fn($item) => array_column($item->children[0] ?? [], 'id'), $topNavItems[0]) // Add all child menu IDs
                );

                $menuItemIds = array_unique($menuItemIds);
                // Fetch all menu items at once
                $menuItems = MenuItem::whereIn('id', $menuItemIds)->get()->keyBy('id');

                // Populate menu item details
                foreach ($topNavItems[0] as $key => $menu) {
                    if (isset($menuItems[$menu->id])) {
                        $menu->title = $menuItems[$menu->id]->title;
                        $menu->slug = $menuItems[$menu->id]->slug;
                        $menu->target = $menuItems[$menu->id]->target;
                        $menu->type = $menuItems[$menu->id]->type;
                        if (!empty($menu->children[0])) {
                            foreach ($menu->children[0] as $child) {
                                if (isset($menuItems[$child->id])) {
                                    $child->title = $menuItems[$child->id]->title;
                                    $child->slug = $menuItems[$child->id]->slug;
                                    $child->target = $menuItems[$child->id]->target;
                                    $child->type = $menuItems[$child->id]->type;
                                } else if (!isset($menuItems[$child->id])) {
                                    $menu->children[0] = null;
                                }
                            }
                        }
                    } elseif (!isset($menuItems[$menu->id])) {
                        unset($topNavItems[0][$key]);
                    }
                }
                return array_values($topNavItems[0]);
            }
        }

        return '';
    }
}


if (!function_exists('themeDefaultSection')) {
    function themeDefaultSection($themeId, $storeId)
    {
        $data = [
            ['section_name' => 'header', 'order' => '0'],
            ['section_name' => 'slider', 'order' => '1'],
            ['section_name' => 'category', 'order' => '2'],
            ['section_name' => 'variant_background', 'order' => '3'],
            ['section_name' => 'bestseller_slider', 'order' => '4'],
            ['section_name' => 'product_category', 'order' => '5'],
            ['section_name' => 'best_product', 'order' => '6'],
            ['section_name' => 'best_product_second', 'order' => '7'],
            ['section_name' => 'product', 'order' => '8'],
            ['section_name' => 'product_banner_slider', 'order' => '9'],
            ['section_name' => 'logo_slider', 'order' => '10'],
            ['section_name' => 'best_selling_slider', 'order' => '11'],
            ['section_name' => 'newest_category', 'order' => '12'],
            ['section_name' => 'feature_product', 'order' => '13'],
            ['section_name' => 'background_image', 'order' => '14'],
            ['section_name' => 'modern_product', 'order' => '15'],
            ['section_name' => 'category_slider', 'order' => '16'],
            ['section_name' => 'service_section', 'order' => '17'],
            ['section_name' => 'subscribe', 'order' => '18'],
            ['section_name' => 'review', 'order' => '19'],
            ['section_name' => 'blog', 'order' => '20'],
            ['section_name' => 'articel_blog', 'order' => '21'],
            ['section_name' => 'top_product', 'order' => '22'],
            ['section_name' => 'video', 'order' => '23'],
            ['section_name' => 'footer', 'order' => '24'],
        ];

        $themeSectionQuery = ThemeSection::query();
        $themeSectionDraftQuery = ThemeSectionDraft::query();
        foreach ($data as $value) {
            $existSection = (clone $themeSectionQuery)->where('section_name', $value['section_name'])->where('store_id', $storeId)->where('theme_id', $themeId)->first();
            if (!$existSection) {
                (clone $themeSectionQuery)->create([
                    'section_name' => $value['section_name'],
                    'store_id' => $storeId,
                    'theme_id' => $themeId,
                    'order' => $value['order'],
                    'is_hide' => 0,
                ]);
            }

            $existDraftSection = (clone $themeSectionDraftQuery)->where('section_name', $value['section_name'])->where('store_id', $storeId)->where('theme_id', $themeId)->first();
            if (!$existDraftSection) {
                (clone $themeSectionDraftQuery)->create([
                    'section_name' => $value['section_name'],
                    'store_id' => $storeId,
                    'theme_id' => $themeId,
                    'order' => $value['order'],
                    'is_hide' => 0,
                ]);
            }
        }
        return true;
    }
}

if (!function_exists('SetDateFormat')) {
    function SetDateFormat($date = '')
    {
        $date_format = Utility::GetValueByName('date_format');
        if (empty($date_format)) {
            $date_format = 'Y-m-d';
        }
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        try {
            $date_new = date($date_format, strtotime($date));
        } catch (\Throwable $th) {
            $date_new = $date;
        }
        return $date_new;
    }
}

if (!function_exists('getThemeMainOrDraftSectionJson')) {
    function getThemeMainOrDraftSectionJson($is_publish, $currentTheme, $sectionName, $storeId)
    {
        $theme_id = $currentTheme;
        $store_id = $storeId;
        // Set json file path based on the section name
        $jsonFilePath = base_path("themes/{$currentTheme}/theme_json/{$sectionName}.json");
        if (!file_exists($jsonFilePath)) {
            $jsonFilePath = base_path("/theme_json/{$sectionName}.json");
        }

        // Decode json file content
        $json_data = file_exists($jsonFilePath) ? json_decode(json_encode(json_decode(file_get_contents($jsonFilePath), true))) : [];
        if (is_array($json_data)) {
            $json_data = arrayToObject($json_data);
        }
        // Get section data based on section name and publication status
        $databaseSection = null;
        $produtcs = $categories = $menus = [];

        switch ($sectionName) {
            case 'slider':
                $databaseSection = getDatabaseSection($is_publish, ThemeSliderSection::query(), ThemeSliderSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'category':
                $categories = Cache::rememberForever('categories_' . $currentTheme . '_' . $storeId, function ()  use ($currentTheme, $storeId) {
                    return MainCategory::where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)
                        ->get();
                });
                $databaseSection = getDatabaseSection($is_publish, ThemeCategorySection::query(), ThemeCategorySectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'bestseller_slider':
                $produtcs = Product::select('id', 'name')->get();
                $produtcs = Cache::rememberForever('produtcs_' . $currentTheme . '_' . $storeId, function ()  use ($currentTheme, $storeId) {
                    return Product::select('id', 'name')->where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)->get();
                });
                $categories = Cache::rememberForever('categories_array_' . $currentTheme . '_' . $storeId, function ()  use ($currentTheme, $storeId) {
                    return MainCategory::where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)
                        ->pluck('name', 'id');
                });
                //$categories = MainCategory::select('id','name')->get();
                $databaseSection = getDatabaseSection($is_publish, ThemeBestsellerSliderSection::query(), ThemeBestsellerSliderSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'modern_product':
                $databaseSection = getDatabaseSection($is_publish, ThemeModernProductSection::query(), ThemeModernProductSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'header':
                $menus = Cache::rememberForever('menus_' . $currentTheme . '_' . $storeId, function ()  use ($currentTheme, $storeId) {
                    return Menu::select('id', 'name')->where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)->get();
                });
                $databaseSection = getDatabaseSection($is_publish, ThemeHeaderSection::query(), ThemeHeaderSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'product_category':
                $categories = Cache::rememberForever('categories_array_' . $currentTheme . '_' . $storeId, function ()  use ($currentTheme, $storeId) {
                    return MainCategory::where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)
                        ->pluck('name', 'id');
                });
                $databaseSection = getDatabaseSection($is_publish, ThemeProductCategorySection::query(), ThemeProductCategorySectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'product':
                $categories = Cache::rememberForever('categories_array_' . $currentTheme . '_' . $storeId, function ()  use ($currentTheme, $storeId) {
                    return MainCategory::where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)
                        ->pluck('name', 'id');
                });
                $databaseSection = getDatabaseSection($is_publish, ThemeProductSection::query(), ThemeProductSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'variant_background':
                $databaseSection = getDatabaseSection($is_publish, ThemeVariantBackgroundSection::query(), ThemeVariantBackgroundSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'subscribe':
                $databaseSection = getDatabaseSection($is_publish, ThemeSubscribeSection::query(), ThemeSubscribeSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'best_product':
                $databaseSection = getDatabaseSection($is_publish, ThemeBestProductSection::query(), ThemeBestProductSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'best_product_second':
                $databaseSection = getDatabaseSection($is_publish, ThemeBestProductSecondSection::query(), ThemeBestProductSecondSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'best_selling_slider':
                $databaseSection = getDatabaseSection($is_publish, ThemeBestSellingSection::query(), ThemeBestSellingSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'review':
                $databaseSection = getDatabaseSection($is_publish, ThemeReviewSection::query(), ThemeReviewSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'blog':
                $databaseSection = getDatabaseSection($is_publish, ThemeBlogSection::query(), ThemeBlogSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'logo_slider':
                $databaseSection = getDatabaseSection($is_publish, ThemeLogoSliderSection::query(), ThemeLogoSliderSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'product_banner_slider':
                $databaseSection = getDatabaseSection($is_publish, ThemeProductBannerSliderSection::query(), ThemeProductBannerSliderSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'newest_product':
                $databaseSection = getDatabaseSection($is_publish, ThemeNewestProductSection::query(), ThemeNewestProductSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'top_product':
                $databaseSection = getDatabaseSection($is_publish, ThemeTopProductSection::query(), ThemeTopProductSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'articel_blog':
                $databaseSection = getDatabaseSection($is_publish, ThemeArticelBlogSection::query(), ThemeArticelBlogSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'service_section':
                $databaseSection = getDatabaseSection($is_publish, ThemeServiceSection::query(), ThemeServiceSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'newest_category':
                $databaseSection = getDatabaseSection($is_publish, ThemeNewestCateorySection::query(), ThemeNewestCateorySectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'video':
                $databaseSection = getDatabaseSection($is_publish, ThemeVideoSection::query(), ThemeVideoSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
            case 'footer':
                $menus = Cache::rememberForever('menus_' . $currentTheme . '_' . $storeId, function () use ($currentTheme, $storeId) {
                    return Menu::select('id', 'name')->where('theme_id', $currentTheme)
                        ->where('store_id', $storeId)->get();
                });
                $databaseSection = getDatabaseSection($is_publish, ThemeFooterSection::query(), ThemeFooterSectionDraft::query(), $sectionName, $currentTheme, $storeId);
                break;
        }


        if (isset($databaseSection)) {
            $json_data = mergeThemeJson($json_data, $databaseSection);
        } else {
            $json_data = arrayToObject(array: objectToArray($json_data));
        }
        
        if (isset($json_data->section_name)) {
            $json_data->section_name = __($json_data->section_name);
        }
        if (isset($json_data->section)) {
            foreach ($json_data->section as &$section) {
                if (isset($section->lable)) {
                    $section->lable = __($section->lable);
                }
            }
        }
        return compact('json_data', 'produtcs', 'categories', 'menus', 'currentTheme', 'theme_id', 'store_id');
    }
}

if (!function_exists('getDatabaseSection')) {
    function getDatabaseSection($is_publish, $sectionModel, $draftModel, $sectionName, $currentTheme, $storeId)
    {
        if ($is_publish) {
            return $sectionModel->where('theme_id', $currentTheme)
                ->where('store_id', $storeId)
                ->where('section_name', $sectionName)
                ->value('theme_json');
        } else {
            $data = $draftModel->where('theme_id', $currentTheme)
                ->where('store_id', $storeId)
                ->where('section_name', $sectionName)
                ->value('theme_json');
            if (!$data) {
                $data = $sectionModel->where('theme_id', $currentTheme)
                    ->where('store_id', $storeId)
                    ->where('section_name', $sectionName)
                    ->value('theme_json');
            }

            return $data;
        }
    }
}

if (!function_exists('pixelSourceCode')) {
    function pixelSourceCode($platform, $pixelId)
    {
        // Facebook Pixel script
        if ($platform === 'facebook') {
            $script = "
                <script>
                    !function(f,b,e,v,n,t,s)
                    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                    n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s)}(window, document,'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                    fbq('init', '%s');
                    fbq('track', 'PageView');
                </script>

                <noscript><img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1'/></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Twitter Pixel script
        if ($platform === 'twitter') {
            $script = "
                <script>
                !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
                },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',
                a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
                twq('config','%s');
                </script>
            ";

            return sprintf($script, $pixelId);
        }

        // LinkedIn Pixel script
        if ($platform === 'linkedin') {
            // Sanitize the pixelId
            $pixelId = htmlspecialchars($pixelId, ENT_QUOTES, 'UTF-8');
            $script = "
                <script type='text/javascript'>
                    _linkedin_data_partner_id = \"%s\";
                </script>
                <script type='text/javascript'>
                    (function () {
                        var s = document.getElementsByTagName('script')[0];
                        var b = document.createElement('script');
                        b.type = 'text/javascript';
                        b.async = true;
                        b.src = 'https://snap.licdn.com/li.lms-analytics/insight.min.js';
                        s.parentNode.insertBefore(b, s);
                    })();
                </script>
                <noscript><img height='1' width='1' style='display:none;' alt='' src='https://dc.ads.linkedin.com/collect/?pid=%s&fmt=gif'/></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Pinterest Pixel script
        if ($platform === 'pinterest') {
            $script = "
                <!-- Pinterest Tag -->
                <script>
                !function(e){if(!window.pintrk){window.pintrk = function () {
                window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
                n=window.pintrk;n.queue=[],n.version='3.0';var
                t=document.createElement('script');t.async=!0;t.src=e;var
                r=document.getElementsByTagName('script')[0];
                r.parentNode.insertBefore(t,r)}}('https://s.pinimg.com/ct/core.js');
                pintrk('load', '%s');
                pintrk('page');
                </script>
                <noscript>
                <img height='1' width='1' style='display:none;' alt=''
                src='https://ct.pinterest.com/v3/?event=init&tid=%s&noscript=1' />
                </noscript>
                <!-- end Pinterest Tag -->
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Quora Pixel script
        if ($platform === 'quora') {
            $script = "
                <script>
                !function (q, e, v, n, t, s) {
                    if (q.qp) return;
                    n = q.qp = function () {
                        n.qp ? n.qp.apply(n, arguments) : n.queue.push(arguments);
                    };
                    n.queue = [];
                    t = document.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = document.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s);
                }(window, 'script', 'https://a.quora.com/qevents.js');
                qp('init', '%s');
                qp('track', 'ViewContent');
                </script>

                <noscript><img height='1' width='1' style='display:none' src='https://q.quora.com/_/ad/%s/pixel?tag=ViewContent&noscript=1'/></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Bing Pixel script
        if ($platform === 'bing') {
            $script = "
                <script>
                (function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:'%s'}; o.q=w[u],w[u]=new UET(o),w[u].push('pageLoad')},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=='loaded'&&s!=='complete'||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,'script','https://bat.bing.com/bat.js','uetq');
                </script>
                <noscript><img src='https://bat.bing.com/action/0?ti=%s&Ver=2' height='0' width='0' style='display:none; visibility:hidden;' /></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Google AdWords Pixel script
        if ($platform === 'google-adwords') {
            $script = "
                <script type='text/javascript'>
                    var google_conversion_id = '%s';
                    var google_custom_params = window.google_tag_params;
                    var google_remarketing_only = true;
                </script>
                <script type='text/javascript' src='//www.googleadservices.com/pagead/conversion.js'></script>
                <noscript>
                <div style='display:inline;'>
                    <img height='1' width='1' style='border-style:none;' alt='' src='//googleads.g.doubleclick.net/pagead/viewthroughconversion/%s/?guid=ON&script=0'/>
                </div>
                </noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Google Analytics Pixel script
        if ($platform === 'google-analytics') {
            $script = "
                <script async src='https://www.googletagmanager.com/gtag/js?id=%s'></script>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '%s');
                </script>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Snapchat Pixel script
        if ($platform === 'snapchat') {
            $script = "
                <script type='text/javascript'>
                (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
                {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
                a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
                r.src=n;var u=t.getElementsByTagName(s)[0];
                u.parentNode.insertBefore(r,u);})(window,document,
                'https://sc-static.net/scevent.min.js');

                snaptr('init', '%s', {
                'user_email': '__INSERT_USER_EMAIL__'
                });

                snaptr('track', 'PAGE_VIEW');
                </script>
            ";

            return sprintf($script, $pixelId);
        }

        // TikTok Pixel script
        if ($platform === 'tiktok') {
            $script = "
                <script>
                !function (w, d, t) {
                w.TiktokAnalyticsObject=t;
                var ttq=w[t]=w[t]||[];
                ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
                for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;
                n++)ttq.setAndDefer(e,ttq.methods[n]);
                return e},ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';
                ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};
                var o=document.createElement('script');o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;
                var a=document.getElementsByTagName('script')[0];a.parentNode.insertBefore(o,a)};
                ttq.load('%s');
                ttq.page();
                }(window, document, 'ttq');
                </script>
            ";

            return sprintf($script, $pixelId);
        }

        return ''; // Return empty string if platform not matched
    }
}

if (!function_exists('metaKeywordSetting')) {
    function metaKeywordSetting($metakeyword = '', $metadesc = '', $metaimage = '', $slug = '')
    {
        $url = route('landing_page', $slug);
        $script = "
        <meta http-equiv='Content-Security-Policy' content='upgrade-insecure-requests'>
        <meta name='title' content='$metakeyword'>
        <meta name='description' content='$metadesc'>

        <meta property='og:type' content='website'>
        <meta property='og:url' content='$url'>
        <meta property='og:title' content=' $metakeyword'>
        <meta property='og:description' content='$metadesc'>
        <meta property='og:image' content=' $metaimage'>

        <meta property='twitter:card' content='summary_large_image'>
        <meta property='twitter:url' content='$url'>
        <meta property='twitter:title' content=' $metakeyword'>
        <meta property='twitter:description' content=' $metadesc'>
        <meta property='twitter:image' content=' $metaimage'>
        ";

        return sprintf($script);
    }
}

if (!function_exists('multi_upload_file')) {
    function multi_upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                    $max_size = !empty($storage_settings['wasabi_max_upload_size']) ? $storage_settings['wasabi_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['wasabi_storage_validation']) ? $storage_settings['wasabi_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                    $max_size = !empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else {
                    $max_size = !empty($storage_settings['local_storage_max_upload_size']) ? $storage_settings['local_storage_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['local_storage_validation']) ? $storage_settings['local_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                }
                $file = $request;
                $key_validation = $key_name . '*';
                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make(array($key_name => $request), [
                    $key_validation => $validation
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {
                    $name = $name;
                    $save = Storage::disk($storage_settings['storage_setting'])->putFileAs(
                        $path,
                        $file,
                        $name
                    );
                    if ($storage_settings['storage_setting'] == 'wasabi') {
                        $url = $save;
                    } elseif ($storage_settings['storage_setting'] == 's3') {
                        $url = $save;
                    } else {
                        $url = 'uploads/' . $save;
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $url
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => 'not set configration',
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
}


if (!function_exists('getHomePageDatabaseSectionDataFromDatabase')) {
    function getHomePageDatabaseSectionDataFromDatabase($data)
    {
        // Cache key based on theme_id and store_id
        $cacheKey = "home_page_data_{$data['theme_id']}_{$data['store_id']}";

        // Attempt to retrieve data from cache
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        // Fetch common data once to avoid multiple queries
        $mainCategoryQuery = MainCategory::where('theme_id', $data['theme_id'])
            ->where('store_id', $data['store_id'])
            ->where('status', 1);

        $categories = (clone $mainCategoryQuery)->where('trending', 1)->limit(4)->get();

        $best_categories = (clone $mainCategoryQuery)->where('trending', 1)->get();

        $productQuery = Product::where('theme_id', $data['theme_id'])
            ->where('store_id', $data['store_id'])
            ->where('product_type', null)
            ->where('status', 1);

        // Load discount products
        $discount_products = (clone $productQuery)->orderByDesc('sale_price')->limit(12)->get();

        // Load product information
        $all_slider_products = $products = (clone $productQuery)->get();
        $all_products = (clone $productQuery)->inRandomOrder()->limit(20)->get();
        $modern_products = (clone $productQuery)->orderByDesc('created_at')->limit(6)->get();
        $home_products = (clone $productQuery)->orderBy('created_at')->limit(4)->get();
        $home_page_products = (clone $productQuery)->orderByDesc('created_at')->limit(2)->get();

        // Load reviews
        $reviews = Testimonial::where('status', 1)
            ->where('theme_id', $data['theme_id'])
            ->where('store_id', $data['store_id'])
            ->get();

        // Load main category list and subcategory information
        $category_id = $mainCategoryQuery->pluck('id')->toArray();

        $MainCategoryList = $mainCategoryQuery->with('product_details')->get();
        // dd($MainCategoryList);
        $SubCategoryList = SubCategory::where('status', 1)
            ->where('theme_id', $data['theme_id'])
            ->where('store_id', $data['store_id'])
            ->get();

        $has_subcategory = Utility::themeSubcategory($data['theme_id'], $data['store_id']);

        // Assign landing_product, latest_product, and random_product correctly
        $latest_product = (clone $productQuery)->orderBy('created_at', 'DESC')->first(); // Define latest_product
        $landing_product = (clone $productQuery)->orderBy('created_at', 'DESC')->first();
        $random_product = (clone $productQuery)->inRandomOrder()->first();

        // Bestseller
        $bestSeller_fun = Product::bestseller_guest($data['theme_id'], $data['store_id'], 12, 'web');
        $bestSeller = $bestSeller_fun['status'] === "success" ? $bestSeller_fun['bestseller_array'] : [];

        // Search products
        $search_products = Product::where('theme_id', $data['theme_id'])
            ->where('store_id', $data['store_id'])
            ->pluck('name', 'id');
        $MainCategory = $MainCategoryList;
        // Prepare data to return and cache it
        $resultData = compact(
            'categories',
            'latest_product',
            'landing_product',
            'search_products',
            'MainCategoryList',
            'SubCategoryList',
            'reviews',
            'category_id',
            'has_subcategory',
            'discount_products',
            'products',
            'all_products',
            'modern_products',
            'home_products',
            'home_page_products',
            'random_product',
            'bestSeller',
            'all_slider_products',
            'best_categories',
            'MainCategory'
        );

        // Store the result in cache for future requests
        Cache::put($cacheKey, $resultData, 3600); // Cache for 1 hour

        return $resultData;
    }
}

if (!function_exists('GetCurrency')) {
    function GetCurrency()
    {
        return Utility::GetValueByName('CURRENCY');
    }
}

if (!function_exists('getProductSlug')) {
    function getProductSlug($productId)
    {
        $product = Cache::remember('product_' . $productId, 3600, function () use ($productId) {
            return Product::where('id', $productId)->select('id', 'name', 'slug')->first();
        });

        return $product->slug ?? null;
    }
}

if (!function_exists('defaultSetting')) {
    function defaultSetting($themeId, $storeId, $type, $user)
    {
        $settingQuery = Setting::query();
        if ($type == 'super admin') {
            $settings = [
                "logo_dark" => "storage/uploads/logo/logo-dark.png",
                "logo_light" => "storage/uploads/logo/logo-light.png",
                "favicon" => "storage/uploads/logo/favicon.png",
                "title_text" => !empty(env('APP_NAME')) ? env('APP_NAME') : 'eCommerceGo SaaS',
                "footer_text" => "Copyright © " . (!empty(env('APP_NAME')) ? env('APP_NAME') : 'eCommerceGo SaaS'),
                "site_date_format" => "M j, Y",
                "site_time_format" => "g:i A",
                "SITE_RTL" => "off",
                "display_landing" => "on",
                "SIGNUP" => "on",
                "email_verification" => "off",
                "color" => "theme-3",
                "cust_theme_bg" => "on",
                "cust_darklayout" => "off",

                "storage_setting" => "local",
                "local_storage_validation" => "jpg,jpeg,png,csv,svg,pdf",
                "local_storage_max_upload_size" => "2048000",
                's3_key' => '',
                's3_secret' => '',
                's3_region' => '',
                's3_bucket' => '',
                's3_endpoint' => '',
                's3_max_upload_size' => '',
                's3_storage_validation' => '',
                'wasabi_key' => '',
                'wasabi_secret' => '',
                'wasabi_region' => '',
                'wasabi_bucket' => '',
                'wasabi_url' => '',
                'wasabi_root' => '',
                'wasabi_max_upload_size' => '',
                'wasabi_storage_validation' => '',

                "CURRENCY_NAME" => "USD",
                "CURRENCYCURRENCY" => "$",
                "currency_format" => "1",
                "defult_currancy" => "USD",
                "defult_language" => "en",
                "defult_timezone" => "Asia/Kolkata",

                // for cookie
                'enable_cookie' => 'on',
                'cookie_logging' => 'on',
                'necessary_cookies' => 'on',
                'cookie_title' => 'We use cookies!',
                'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
                'strictly_cookie_title' => 'Strictly necessary cookies',
                'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
                'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
                "more_information_title" => "",
                'contactus_url' => '#',

            ];
        } else {
            $superAdmin = User::where('type', 'super admin')->first();
            if ($superAdmin) {
                $superAdminSetting = (clone $settingQuery)->where('created_by', $superAdmin->id)->pluck('value', 'name')->toArray();
                $logo_dark = $superAdminSetting['logo_dark'] ?? "storage/uploads/logo/logo-dark.png";
                $logo_light = $superAdminSetting['logo_light'] ?? "storage/uploads/logo/logo-light.png";
                $favicon = $superAdminSetting['favicon'] ?? "storage/uploads/logo/favicon.png";
                $title_text = $superAdminSetting['title_text'] ?? (!empty(env('APP_NAME')) ? env('APP_NAME') : 'eCommerceGo SaaS');
                $footer_text = $superAdminSetting['footer_text'] ?? ("Copyright © " . (!empty(env('APP_NAME')) ? env('APP_NAME') : 'eCommerceGo SaaS'));
                $site_date_format = $superAdminSetting['site_date_format'] ?? "M j, Y";
                $site_time_format = $superAdminSetting['site_time_format'] ?? "g:i A";
                $SITE_RTL = $superAdminSetting['SITE_RTL'] ?? "off";
                $color = $superAdminSetting['color'] ?? "theme-3";
                $cust_theme_bg = $superAdminSetting['cust_theme_bg'] ?? "on";
                $cust_darklayout = $superAdminSetting['cust_darklayout'] ?? "off";
                $CURRENCY_NAME = $superAdminSetting['CURRENCY_NAME'] ?? "USD";
                $CURRENCYCURRENCY = $superAdminSetting['CURRENCYCURRENCY'] ?? "$";
                $currency_format = $superAdminSetting['currency_format'] ?? "1";
                $defult_language = $superAdminSetting['defult_language'] ?? "en";
                $defult_currancy = $superAdminSetting['defult_currancy'] ?? "USD";
                $defult_timezone = $superAdminSetting['defult_timezone'] ?? "Asia/Kolkata";

                // $user->update(['language' => ($superAdmin->language ?? 'en')]);
            } else {
                $logo_dark = "storage/uploads/logo/logo-dark.png";
                $logo_light = "storage/uploads/logo/logo-light.png";
                $favicon = "storage/uploads/logo/favicon.png";
                $title_text = (!empty(env('APP_NAME')) ? env('APP_NAME') : 'eCommerceGo SaaS');
                $footer_text = ("Copyright © " . (!empty(env('APP_NAME')) ? env('APP_NAME') : 'eCommerceGo SaaS'));
                $site_date_format = "M j, Y";
                $site_time_format = "g:i A";
                $SITE_RTL = "off";
                $color = "theme-3";
                $cust_theme_bg = "on";
                $cust_darklayout = "off";
                $CURRENCY_NAME = "USD";
                $CURRENCYCURRENCY = "$";
                $currency_format = "1";
                $defult_language = "en";
                $defult_currancy = "USD";
                $defult_timezone = "Asia/Kolkata";
                //$user->update(['language' => 'en']);
            }
            $settings = [
                "logo_dark" => $logo_dark,
                "logo_light" => $logo_light,
                "favicon" => $favicon,
                "title_text" => $title_text,
                "footer_text" => $footer_text,
                "site_date_format" => $site_date_format,
                "site_time_format" => $site_time_format,
                "SITE_RTL" => $SITE_RTL,
                "display_landing" => "on",
                "SIGNUP" => "on",
                "email_verification" => "off",
                "color" => $color,
                "cust_theme_bg" => $cust_theme_bg,
                "cust_darklayout" => $cust_darklayout,

                "CURRENCY_NAME" => $CURRENCY_NAME,
                "CURRENCYCURRENCY" => $CURRENCYCURRENCY,
                "currency_format" => $currency_format,
                "defult_currancy" => $defult_currancy,
                "defult_language" => $defult_language,
                "defult_timezone" => $defult_timezone,

                // for cookie
                'enable_cookie' => 'on',
                'cookie_logging' => 'on',
                'necessary_cookies' => 'on',
                'cookie_title' => 'We use cookies!',
                'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
                'strictly_cookie_title' => 'Strictly necessary cookies',
                'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
                'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
                "more_information_title" => "",
                'contactus_url' => '#',

            ];
        }


        foreach ($settings as $key => $value) {
            $exist = (clone $settingQuery)->where('name', $key)->where('theme_id', $themeId)->where('store_id', $storeId)->first();
            if (!$exist) {
                (clone $settingQuery)->create([
                    'name' => $key,
                    'value' => (string) $value,
                    'theme_id' => $themeId,
                    'store_id' => $storeId,
                    'created_by' => $user->id
                ]);
            }
        }
        Utility::WhatsappMeassage($user->id);
        return true;
    }
}

if (!function_exists('currency_format_with_sym')) {

    function currency_format_with_sym($price, $store_id = null, $theme_id = null, $productId = null)
    {
        if (!empty($store_id) && empty($theme_id)) {
            $company_settings = getAdminAllSetting(null, $store_id, null);
        } elseif (!empty($store_id) && !empty($theme_id)) {
            $company_settings = getAdminAllSetting(null, $store_id, $theme_id);
        } else {
            $company_settings = getAdminAllSetting();
        }

        if (!empty($productId)) {
            $store = Store::where('id', $store_id)->first();
            $product = Product::where('id', $productId)->first();
            if ($product && $store) {
                $price = Product::ProductPrice($theme_id, $store->slug, $product->id, $product->variant_id);
            }
        }

        $symbol_position = 'pre';
        $currancy_symbol = '$';
        $format = '1';
        $number = explode('.', $price);
        $length = strlen(trim($number[0]));

        if ($length > 3) {
            $decimal_separator = isset($company_settings['decimal_separator']) && $company_settings['decimal_separator'] === 'dot' ? '.' : ',';
            $thousand_separator = isset($company_settings['thousand_separator']) && $company_settings['thousand_separator'] === 'dot' ? '.' : ',';
        } else {
            $decimal_separator = isset($company_settings['decimal_separator']) && $company_settings['decimal_separator'] === 'dot' ? '.' : ',';

            $thousand_separator = isset($company_settings['thousand_separator'])  && $company_settings['thousand_separator'] === 'dot' ? '.' : ',';
        }
        if (isset($company_settings['site_currency_symbol_position'])) {
            $symbol_position = $company_settings['site_currency_symbol_position'];
        }
        if (isset($company_settings['defult_currancy_symbol'])) {
            $currancy_symbol = $company_settings['defult_currancy_symbol'];
        }
        if (isset($company_settings['currency_format'])) {
            $format = $company_settings['currency_format'];
        } else {
            $format = 2;
        }
        if (isset($company_settings['currency_space'])) {
            $currency_space = isset($company_settings['currency_space']) ? $company_settings['currency_space'] : '';
        }
        if (isset($company_settings['site_currency_symbol_name'])) {
            $defult_currancy = $company_settings['defult_currancy'];
            $defult_currancy_symbol = $company_settings['defult_currancy_symbol'];
            $currancy_symbol = $company_settings['site_currency_symbol_name'] == 'symbol' ? $defult_currancy_symbol : $defult_currancy;
        }
        return (
            ($symbol_position == "pre") ? $currancy_symbol : '') . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '')
            . number_format($price, $format, $decimal_separator, $thousand_separator) . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '') .
            (($symbol_position == "post") ? $currancy_symbol : '');
    }
}


// module alias name
if (!function_exists('Module_Alias_Name')) {
    function Module_Alias_Name($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (count($addons) == 0 && count($resultArray) == 0) {
            $addons = AddOnManager::all()->toArray();
            $resultArray = array_reduce($addons, function ($carry, $item) {
                // Check if both "module" and "name" keys exist in the current item
                if (isset($item['module']) && isset($item['name'])) {
                    // Add a new key-value pair to the result array
                    $carry[$item['module']] = $item['name'];
                }
                return $carry;
            }, []);
        }

        $module = Module::find($module_name);
        if (isset($resultArray)) {
            $module_name = array_key_exists($module_name, $resultArray) ? $resultArray[$module_name] : (!empty($module) ? ($module->alias ?? $module->name) : $module_name);
        } elseif (!empty($module)) {
            $module_name = $module->alias ?? $module->name;
        }
        return $module_name;
    }
}

if (!function_exists('get_permission_by_module')) {
    function get_permission_by_module($module)
    {
        $user = auth()->user();

        if ($module == 'General') {
            $permissions = Permission::where('module', 'Base')->orderBy('name')->get();
        } else {
            $permissions = Permission::where('module', $module)->orderBy('name')->get();
        }
        return $permissions;
    }
}

if (!function_exists('sideMenuCacheForget')) {
    function sideMenuCacheForget($type = null, $user_id = null)
    {
        if ($type == 'all') {
            Cache::flush();
        }

        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = auth()->user();
        }
        if ($user->type == 'admin') {
            $users = User::select('id')->where('created_by', $user->id)->pluck('id');
            foreach ($users as $id) {
                try {
                    $key = 'sidebar_menu_' . $id;
                    Cache::forget($key);
                } catch (\Exception $e) {
                    \Log::error('comapnySettingCacheForget :' . $e->getMessage());
                }
            }
            try {
                $key = 'sidebar_menu_' . $user->id;
                Cache::forget($key);
            } catch (\Exception $e) {
                \Log::error('comapnySettingCacheForget :' . $e->getMessage());
            }
            return true;
        }

        try {
            $key = 'sidebar_menu_' . $user->id;
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('comapnySettingCacheForget :' . $e->getMessage());
        }

        return true;
    }
}

if (!function_exists('module_is_active')) {
    function module_is_active($module, $user_id = null)
    {
        // Check if the module exists and is enabled
        if (Module::has($module)) {
            $module = Module::find($module);

            if ($module->isEnabled()) {
                // Check for store slug in the route
                if (request()->route('storeSlug') || request()->route('slug')) {
                    $slug = request()->route('storeSlug') ?? request()->route('slug');

                    // Cache the store query to reduce calls
                    $store = Cache::remember("store_{$slug}", 3600, function () use ($slug) {
                        return Store::where('slug', $slug)->first();
                    });

                    if ($store) {
                        // Cache the user query by store's `created_by`
                        $user = Cache::remember("user_by_store_{$store->created_by}", 3600, function () use ($store) {
                            return User::where('id', $store->created_by)->first();
                        });

                        if ($user) {
                            // Cache the plan query for the user
                            $plan = Cache::remember("plan_by_user_{$user->plan_id}", 3600, function () use ($user) {
                                return Plan::where('id', $user->plan_id)->first();
                            });

                            if ($plan) {
                                $modules = explode(',', $plan->modules);

                                if (in_array($module->name, $modules)) {
                                    return true;
                                }
                            }

                            return false;
                        }
                        return false;
                    } elseif ($module->name == 'LandingPage') {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    // If no slug is available, check for authenticated user or specific user_id
                    if (Auth::check()) {
                        $user = Auth::user();
                    } elseif ($user_id != null) {
                        $user = Cache::remember("user_{$user_id}", 3600, function () use ($user_id) {
                            return User::find($user_id);
                        });
                    }
                    if (!empty($user)) {
                        // Super admin can always access all modules
                        if ($user->type == 'super admin') {
                            return true;
                        } else {
                            $active_module = ActivatedModule($user->id);

                            if (count($active_module) > 0 && in_array($module->name, $active_module)) {
                                return true;
                            }
                            return false;
                        }
                    }
                    return false;
                }
            }
            return false;
        }
        return false;
    }
}

if (!function_exists('ActivatedModule')) {
    function ActivatedModule($user_id = null)
    {
        $activated_module = User::$superadmin_activated_module;

        $user_active_module = [];
        if ($user_id != null) {
            $user = Cache::remember("user_{$user_id}", 3600, function () use ($user_id) {
                return User::find($user_id);
            });
        } elseif (Auth::check()) {
            $user = Auth::user();
        }

        if (!empty($user)) {

            $available_modules = array_values(Module::getByStatus(1));

            // $available_modules =[];

            if ($user->type == 'super admin') {
                $user_active_module = $available_modules;
            } else {
                static $active_module = null;
                if ($user->type != 'admin') {
                    $user_not_com = Cache::remember("user_{$user->created_by}", 3600, function () use ($user) {
                        return User::find($user->created_by);
                    });
                    if (!empty($user)) {
                        if ($active_module == null) {
                            $active_module = Cache::remember("user_active_module_{$user_not_com->id}", 3600, function () use ($user_not_com) {
                                return userActiveModule::where('user_id', $user_not_com->id)->pluck('module')->toArray();
                            });
                        }
                    }
                } else {
                    if ($active_module == null) {
                        $active_module = Cache::remember("user_active_module_{$user->id}", 3600, function () use ($user) {
                            return userActiveModule::where('user_id', $user->id)->pluck('module')->toArray();
                        });
                    }
                }

                // Find the common modules
                $commonModules = array_intersect($active_module, $available_modules);
                $user_active_module = array_unique(array_merge($commonModules, $activated_module));
            }
        }
        return count($user_active_module) > 0 ? $user_active_module : [];
    }
}

if (!function_exists('get_module_img')) {
    function get_module_img($module)
    {
        $url = url("/packages/workdo/" . $module . '/favicon.png');
        return $url;
    }
}

if (!function_exists('admin_setting')) {
    function admin_setting($key)
    {
        if ($key) {
            $admin_settings = getAdminAllSetting();
            $setting = (array_key_exists($key, $admin_settings)) ? $admin_settings[$key] : null;
            return $setting;
        }
    }
}

if (!function_exists('check_file')) {
    function check_file($path)
    {

        if (!empty($path)) {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting']) && ($storage_settings['storage_setting'] == null || $storage_settings['storage_setting'] == 'local')) {

                return file_exists(base_path($path));
            } else {

                if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                }
                try {
                    return Storage::disk($storage_settings['storage_setting'])->exists($path);
                } catch (\Throwable $th) {
                    return 0;
                }
            }
        } else {
            return 0;
        }
    }
}


// module price name
if (!function_exists('ModulePriceByName')) {
    function ModulePriceByName($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (count($addons) == 0 && count($resultArray) == 0) {
            $addons = AddOnManager::all()->toArray();
            $resultArray = array_reduce($addons, function ($carry, $item) {
                // Check if both "module" and "name" keys exist in the current item
                if (isset($item['module'])) {
                    // Add a new key-value pair to the result array
                    $carry[$item['module']]['monthly_price'] = $item['monthly_price'];
                    $carry[$item['module']]['yearly_price'] = $item['yearly_price'];
                }
                return $carry;
            }, []);
        }

        $module = Module::find($module_name);
        $data = [];
        $data['monthly_price'] = 0;
        $data['yearly_price'] = 0;

        if (!empty($module)) {
            $path = $module->getPath() . '/module.json';
            $json = json_decode(file_get_contents($path), true);

            $data['monthly_price'] = (isset($json['monthly_price']) && !empty($json['monthly_price'])) ? $json['monthly_price'] : 0;
            $data['yearly_price'] = (isset($json['yearly_price']) && !empty($json['yearly_price'])) ? $json['yearly_price'] : 0;
        }

        if (isset($resultArray)) {
            $data['monthly_price'] = isset($resultArray[$module_name]['monthly_price']) ? $resultArray[$module_name]['monthly_price'] : $data['monthly_price'];
            $data['yearly_price'] = isset($resultArray[$module_name]['yearly_price']) ? $resultArray[$module_name]['yearly_price'] : $data['yearly_price'];
        }

        return $data;
    }
}

if (!function_exists('getshowModuleList')) {
    function getshowModuleList($status = false)
    {
        $all = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            if (!isset($module->display) || $module->display) {
                array_push($list, $module->name);
            }
        }
        return $list;
    }
}

if (!function_exists('sidebar_logo')) {
    function sidebar_logo()
    {
        $admin_settings = getSuperAdminAllSetting();
        if (\Auth::check() && (\Auth::user()->type != 'super admin')) {
            $company_settings = getAdminAllSetting();

            if ((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on') {
                if (!empty($company_settings['logo_light'])) {
                    if (check_file($company_settings['logo_light'])) {
                        return $company_settings['logo_light'];
                    } else {
                        return 'storage/uploads/logo/logo-light.png';
                    }
                } else {
                    if (!empty($admin_settings['logo_light'])) {
                        if (check_file($admin_settings['logo_light'])) {
                            return $admin_settings['logo_light'];
                        } else {
                            return 'storage/uploads/logo/logo-light.png';
                        }
                    } else {
                        return 'storage/uploads/logo/logo-light.png';
                    }
                }
            } else {
                if (!empty($company_settings['logo_dark'])) {
                    if (check_file($company_settings['logo_dark'])) {
                        return $company_settings['logo_dark'];
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                } else {
                    if (!empty($admin_settings['logo_dark'])) {
                        if (check_file($admin_settings['logo_dark'])) {
                            return $admin_settings['logo_dark'];
                        } else {
                            return 'uploads/logo/logo_dark.png';
                        }
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                }
            }
        } else {
            if ((isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') == 'on') {
                if (!empty($admin_settings['logo_light'])) {
                    if (check_file($admin_settings['logo_light'])) {
                        return $admin_settings['logo_light'];
                    } else {
                        return 'storage/uploads/logo/logo-light.png';
                    }
                } else {
                    return 'storage/uploads/logo/logo-light.png';
                }
            } else {
                if (!empty($admin_settings['logo_dark'])) {
                    if (check_file($admin_settings['logo_dark'])) {
                        return $admin_settings['logo_dark'];
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                } else {
                    return 'uploads/logo/logo_dark.png';
                }
            }
        }
    }
}

if (!function_exists('light_logo')) {
    function light_logo()
    {
        if (\Auth::check()) {
            $company_settings = getAdminAllSetting();
            $logo_light = isset($company_settings['logo_light']) ? $company_settings['logo_light'] : 'storage/uploads/logo/logo-light.png';
        } else {
            $admin_settings = getSuperAdminAllSetting();
            $logo_light = isset($admin_settings['logo_light']) ? $admin_settings['logo_light'] : 'storage/uploads/logo/logo-light.png';
        }
        if (check_file($logo_light)) {
            return $logo_light;
        } else {
            return 'uploads/logo/logo_dark.png';
        }
    }
}

if (!function_exists('dark_logo')) {
    function dark_logo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo_dark = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : 'uploads/logo/logo_dark.png';
        } else {
            $admin_settings = getSuperAdminAllSetting();
            $logo_dark = isset($admin_settings['logo_dark']) ? $admin_settings['logo_dark'] : 'uploads/logo/logo_dark.png';
        }
        if (check_file($logo_dark)) {
            return $logo_dark;
        } else {
            return 'uploads/logo/logo_dark.png';
        }
    }
}

if (!function_exists('delete_file')) {
    function delete_file($path)
    {
        if (check_file($path)) {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'local') {
                    return File::delete($path);
                } else {
                    if ($storage_settings['storage_setting'] == 's3') {
                        config(
                            [
                                'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                                'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                                'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                                'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                                'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                                'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                            ]
                        );
                    } else if ($storage_settings['storage_setting'] == 'wasabi') { {
                            config(
                                [
                                    'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                                    'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                                    'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                                    'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                                    'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                                    'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                                ]
                            );
                        }
                        return Storage::disk($storage_settings['storage_setting'])->delete($path);
                    }
                }
            }
        }
    }
}

if (!function_exists('get_size')) {
    function get_size($url)
    {
        $url = str_replace(' ', '%20', $url);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }
}

if (!function_exists('delete_folder')) {
    function delete_folder($path)
    {
        $storage_settings = getAdminAllSetting();
        if (isset($storage_settings['storage_setting'])) {

            if ($storage_settings['storage_setting'] == 'local') {
                if (is_dir(Storage::path($path))) {
                    return \File::deleteDirectory(Storage::path($path));
                }
            } else {
                if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                } else if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                }
                return Storage::disk($storage_settings['storage_setting'])->deleteDirectory($path);
            }
        }
    }
}

if (!function_exists('delete_directory')) {
    function delete_directory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}

if (!function_exists('getModuleList')) {
    function getModuleList()
    {
        $all = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            array_push($list, $module->name);
        }
        return $list;
    }
}

if (!function_exists('default_currency_format_with_sym')) {

    function default_currency_format_with_sym($price, $store_id = null, $theme_id = null)
    {
        if (!empty($store_id) && empty($theme_id)) {
            $company_settings = getSuperAdminAllSetting(null, $store_id, null);
        } elseif (!empty($store_id) && !empty($theme_id)) {
            $company_settings = getSuperAdminAllSetting(null, $store_id, $theme_id);
        } else {
            $company_settings = getSuperAdminAllSetting();
        }
        $symbol_position = 'pre';
        $currancy_symbol = '$';
        $format = '1';
        $number = explode('.', $price);
        $length = strlen(trim($number[0]));
        if ($length > 3) {
            $decimal_separator = isset($company_settings['decimal_separator']) && $company_settings['decimal_separator'] === 'dot' ? '.' : ',';
            $thousand_separator = isset($company_settings['thousand_separator']) && $company_settings['thousand_separator'] === 'dot' ? '.' : ',';
        } else {
            $decimal_separator = isset($company_settings['decimal_separator']) == 'dot' ? '.' : ',';

            $thousand_separator = isset($company_settings['thousand_separator']) == 'dot' ? '.' : ',';
        }
        if (isset($company_settings['site_currency_symbol_position'])) {
            $symbol_position = $company_settings['site_currency_symbol_position'];
        }
        if (isset($company_settings['defult_currancy_symbol'])) {
            $currancy_symbol = $company_settings['defult_currancy_symbol'];
        }
        if (isset($company_settings['currency_format'])) {
            $format = $company_settings['currency_format'];
        } else {
            $format = 2;
        }
        if (isset($company_settings['currency_space'])) {
            $currency_space = isset($company_settings['currency_space']) ? $company_settings['currency_space'] : '';
        }
        if (isset($company_settings['site_currency_symbol_name'])) {
            $defult_currancy = $company_settings['defult_currancy'];
            $defult_currancy_symbol = $company_settings['defult_currancy_symbol'];
            $currancy_symbol = $company_settings['site_currency_symbol_name'] == 'symbol' ? $defult_currancy_symbol : $defult_currancy;
        }

        return (
            ($symbol_position == "pre") ? $currancy_symbol : '') . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '')
            . number_format($price, $format, $decimal_separator, $thousand_separator) . ((isset($currency_space) && $currency_space) == 'withspace' ? ' ' : '') .
            (($symbol_position == "post") ? $currancy_symbol : '');
    }
}

if (!function_exists('getInnerPageJson')) {
    function getInnerPageJson($theme_id, $store_id, $specific_page_name = null)
    {
        $page_names = ['order_complate', 'product_page', 'blog_page', 'cart_page', 'checkout_page', 'contact_page', 'faq_page', 'collection_page', 'article_page', 'common_page']; // Correct the typo
        $default_paths = [
            base_path('theme_json/order-complete.json'),
            base_path('theme_json/product_page.json'),
            base_path('theme_json/blog_page.json'),
            base_path('theme_json/cart_page.json'),
            base_path('theme_json/checkout_page.json'),
            base_path('theme_json/contact_page.json'),
            base_path('theme_json/faq_page.json'),
            base_path('theme_json/collection_page.json'),
            base_path('theme_json/article_page.json'),
            base_path('theme_json/common_page.json')
        ];

        return getInnerPageJsonData($theme_id, $store_id, $page_names, $default_paths, $specific_page_name);
    }
}

if (!function_exists('getInnerPageJsonData')) {
    function getInnerPageJsonData($theme_id, $store_id, $page_names, $default_paths, $specific_page_name = null)
    {
        // Filter page names and default paths if specific page name is provided
        if ($specific_page_name !== null) {
            $index = array_search($specific_page_name, $page_names);
            if ($index === false) {
                return []; // Return empty array if specific page name is not found
            }
            $page_names = [$specific_page_name];
            $default_paths = [$default_paths[$index]];
        }
        $settings = AppSetting::select('page_name', 'theme_json')
            ->where('theme_id', $theme_id)
            ->whereIn('page_name', $page_names)
            ->where('store_id', $store_id)
            ->get()
            ->keyBy('page_name');
        $json_data_array = [];
        foreach ($page_names as $index => $page_name) {
            $default_path = $default_paths[$index];

            if (file_exists($default_path)) {
                // Default data
                $json_data = json_decode(file_get_contents($default_path), true);

                // Override with database settings if available
                if (isset($settings[$page_name])) {
                    $json_data = json_decode($settings[$page_name]->theme_json, true);
                }
            } else {
                // Default data
                $json_data = [];
            }

            if (isset($json_data['section'])) {
                foreach ($json_data['section'] as &$section) {
                    if (isset($section['lable'])) {
                        $section['lable'] = __($section['lable']);
                    }
                }
            }

            if (isset($json_data['section_name'])) {
                $json_data['section_name'] = __($json_data['section_name']);
            }
            $json_data_array[$page_name] = $json_data;
        }

        return $json_data_array;
    }
}

if (!function_exists('getProductActualPrice')) {
    function getProductActualPrice($product, $variant = null)
    {
        $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
            return Store::where('id', getCurrentStore())->first();
        });
        $price = Product::ProductPrice($store->theme_id, $store->slug, $product->id, ($variant->id ?? ($product->variant_id ?? 0)), $product->price);
        $salePrice =  Product::ProductPrice($store->theme_id, $store->slug, $product->id, ($variant->id ?? ($product->variant_id ?? 0)), $product->sale_price);

        if ($price && $salePrice) {
            return ($price > $salePrice) ? $salePrice : $price;
        }

        return $price ?: $salePrice;
    }
}

if (!function_exists('bulkDeleteCloneCheckboxColumn')) {
    function bulkDeleteCloneCheckboxColumn()
    {
        // Initialize an array to hold the columns
        $columns = [];

        // Check if the BulkDelete module is active
        if (module_is_active('BulkDelete')) {
            // Add the checkbox column if the module is active
            $columns[] = \Yajra\DataTables\Html\Column::computed('checkbox')
                ->title('<input type="checkbox" id="select-all">')
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->render('function() {
                    return \'<input type="checkbox" class="select-row" value="\' + this.id + \'">\';
                }');
        }

        return $columns; // Return the array of columns
    }
}

if (!function_exists('bulkDeleteForm')) {
    function bulkDeleteForm($type, $dataTableId)
    {
        return [
            'text' => '<i class="ti ti-trash" data-bs-toggle="tooltip" title="' . __("Bulk Delete") . '" data-bs-original-title="' . __("Bulk Delete") . '"></i>',
            'className' => 'btn btn-light-danger bulk-delete',
            'action' => "function(e, dt, button, config) {
                bulkDelete('{$type}','{$dataTableId}');
            }"
        ];
    }
}

if (!function_exists('getSideMenu')) {
    function getSideMenu()
    {
        $user = auth()->user();
        $menu = new \App\Classes\Menu($user);
        if ($user->type == 'super admin') {
            if (Schema::hasTable('side_menus_option')) {
                $sortedModules = SideMenuOption::where('type', 'super admin')->orderBy('order')->get();
                if ($sortedModules->isEmpty()) {
                    event(new \App\Events\SuperAdminMenuEvent($menu));
                    return generateMenu($menu->menu, null);
                }
            }else{
                event(new \App\Events\SuperAdminMenuEvent($menu));
                return generateMenu($menu->menu, null);
            }
        } else {
            $sideMenuItems = SideMenuOption::where('type', 'admin')->where('created_by', 0)->where('store_id', null)->where('theme_id',null)->orderBy('order')->get();
            $MenuItems = SideMenuOption::where('type', 'admin')->where('created_by', $user->id)->where('store_id', $user->current_store)->where('theme_id',$user->theme_id)->orderBy('order')->get();

            if (!$MenuItems->isEmpty()) {
                $sideMenuItems = $sideMenuItems->merge($MenuItems)->sortBy('order');
            }
            $customOrder = SideMenuOrder::whereIn('menu_id', $sideMenuItems->pluck('id'))
                ->where('created_by', $user->id)
                ->where('store_id', $user->current_store)
                ->where('theme_id', $user->theme_id)
                ->get();

            $orderMap = $customOrder->pluck('order', 'menu_id')->toArray();
            $sortedModules = $sideMenuItems->sortBy(function ($module) use ($orderMap) {
                return isset($orderMap[$module->id]) ? $orderMap[$module->id] : $module->order;
            });
            
            if ($sideMenuItems->isEmpty()) {
                event(new \App\Events\CompanyMenuEvent($menu));
                return generateMenu($menu->menu, null);
            }
        }
        foreach ($sortedModules as $item) {
            if (isset($item->is_enable) && $item->is_enable == 'on') {
                $menu->menu[] = [
                    'id' => $item->id,
                    'category' => $item->category,
                    'title' => $item->title,
                    'icon' => $item->icon,
                    'name' => $item->name,
                    'parent' => $item->parent_id,
                    'order' => isset($orderMap[$item->id]) ? $orderMap[$item->id] : $item->order,
                    'link_type' => $item->link_type ?? null,
                    'route' => $item->link,
                    'module' => $item->module ?? null,
                    'window_type' => $item->window_type ?? null,
                    'is_enable' => $item->is_enable ?? null,
                    'permission' => $item->permission ?? null,
                ];
            }
        }
        return generateSideMenu($menu->menu, null);
    }
}
if (!function_exists('generateSideMenu')) {
    function generateSideMenu($menuItems, $parent = null, $level = 0)
    {
        $html = '';
        $filteredItems = array_filter($menuItems, function ($item) use ($parent) {
            return $item['parent'] == $parent;
        });
        usort($filteredItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        foreach ($filteredItems as $item) {
            if (isset($item['is_enable']) && $item['is_enable'] == 'on') {
                if ($item['name'] == 'mobilescreensetting' && env('IS_MOBILE') != 'yes') {
                    continue;
                }
                $hasChildren = hasChildren($menuItems, $item['name']);
                if ($item['parent'] == null) {
                    $html .= '<li class="dash-item dash-hasmenu">';
                } else {
                    $html .= '<li class="dash-item sub-menu-lnk">';
                }
                $marginLeft = 10 * $level;

                $href = '#!';
                if (isset($item['link_type'])) {
                    switch ($item['link_type']) {
                        case 'internal':
                            $href = !empty((env('APP_URL')) . '/' . $item['route']) ? url((env('APP_URL')) . '/' . $item['route']) : '#';
                            break;

                        case 'external':
                            if (isset($item['route'])) {
                                $routeData = json_decode($item['route'], true);

                                if (isset($routeData['href'][0]) && isset($routeData['type'][0])) {
                                    $baseUrl = $routeData['href'][0];
                                    $domain = $routeData['type'][0];
                                    $href = $baseUrl . $domain;
                                } else {
                                    $href = '#';
                                }
                            } else {
                                $href = '#';
                            }
                            break;

                        case 'hash':
                            $href = '#' . (isset($item['link']) ? $item['link'] : '');
                            break;
                        case 'route':
                            $href = !empty($item['route']) ? route($item['route']) : '#';
                            break;
                    }
                }
                $target = '';
                if (isset($item['window_type'])) {
                    switch ($item['window_type']) {
                        case 'same_window':
                            $target = '';
                            break;

                        case 'new_window':
                            $target = ' target="_blank"';
                            break;

                        case 'iframe':
                            $href = route("sidemenubuilder.iframe", $item['id']);
                            break;
                    }
                }
                $html .= '<a href="' . $href . '" class="dash-link" ' . $target . '>';
                if ($item['parent'] == null) {
                    $html .= ' <span class="dash-micon"><i class="ti ti-' . $item['icon'] . '"></i></span>
                    <span class="dash-mtext">';
                }
                $html .= __($item['title']) . '</span>';
                $buttonHtml = '<div class="d-flex justify-content-between"><div><button type="button" class="collepse-menu-buttons collepse-btn-back btn btn-sm btn-secondary"><i class="ti ti-arrow-back"></i></button></div>
                <div><button type="button" class="collepse-menu-buttons collepse-btn-close btn btn-sm btn-danger"><i class="ti ti-x"></i></button></div></div>';

                if ($hasChildren) {
                    $html .= ' </a><span class="dash-arrow"> <i data-feather="chevron-right"></i> </span>';
                    $html .= '<ul class="dash-submenu sub-menu-dropdown">';
                    $html .= $buttonHtml;
                    $html .= generateSideMenu($menuItems, $item['name'], $level + 1);
                    $html .= '</ul>';
                } else {
                    $html .= '</a>';
                }
                $html .= '</li>';
            }
        }
        return $html;
    }
}

// Function to recursively create and set permissions for each directory in the path
if (!function_exists('createAndSetPermissionsRecursively')) {
    function createAndSetPermissionsRecursively($path, $disk) {
        $segments = explode('/', $path);
        $currentPath = '';

        foreach ($segments as $segment) {
            $currentPath .= ($currentPath ? '/' : '') . $segment;

            if (!\Storage::disk($disk)->exists($currentPath)) {
                $oldUmask = umask(0);
                \Storage::disk($disk)->makeDirectory($currentPath, 0777, true);
                umask($oldUmask);
            }

            if (function_exists('chmod')) {
                $fullPath = \Storage::disk($disk)->path($currentPath);
                @chmod($fullPath, 0777); // Set permissions to the current directory
            }
        }
    }
}