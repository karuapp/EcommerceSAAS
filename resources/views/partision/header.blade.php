@php
$displaylang = App\Models\Utility::languages();
if (auth()->user() && auth()->user()->language) {
$currentLanguage = auth()->user() ? auth()->user()->language : 'en';
} else {
$currentLanguage = Cookie::get('LANGUAGE');
if (empty($currentLanguage)) {
$currentLanguage = auth()->user() ? auth()->user()->language : 'en';
}
}
$store = \App\Models\Store::where('id', getCurrentStore())->first();
$theme_url = App\Http\Controllers\HomeController::getThemeUrl($store);
@endphp

@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
<header class="dash-header transprent-bg">
    @else
    <header class="dash-header">
        @endif
        <div class="header-wrapper">
            <div class="me-auto dash-mob-drp">
                <ul class="list-unstyled gap-2">
                    <li class="dash-h-item mob-hamburger">
                        <a href="#!" class="dash-head-link" id="mobile-collapse">
                            <div class="hamburger hamburger--arrowturn">
                                <div class="hamburger-box">
                                    <div class="hamburger-inner"></div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="dropdown dash-h-item drp-company">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="theme-avtar">
                                <img alt="#" style="height:inherit;"
                                    src="{{ !empty(auth()->user()->profile_image) && file_exists(auth()->user()->profile_image)  ? get_file(auth()->user()->profile_image) : asset(Storage::url('uploads/profile/avatar.png')) }}"
                                    class="header-avtar">

                            </span>
                            <span class="hide-mob ms-2">
                                @if (!Auth::guest())
                                {{ __('Hi, ') }}{{ !empty(Auth::user()) ? Auth::user()->name : '' }}!
                                @else
                                {{ __('Guest') }}
                                @endif
                            </span>
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown">

                            <a href="{{ route('profile') }}" class="dropdown-item">
                                <i class="ti ti-user"></i>
                                <span>{{ __(' Profile') }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" id="form_logout">
                                <a href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="dropdown-item">
                                    <i class="ti ti-power"></i>
                                    @csrf
                                    {{ __(' Log Out') }}
                                </a>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="dash-center-drp">
                <ul class="list-unstyled exit-company-btn">
                    @impersonating($guard = null)
                    <li class="dropdown dash-h-item">
                        <a class="dropdown-item dash-head-link dropdown-toggle arrow-none cust-btn bg-danger"
                            href="{{ route('exit.admin') }}"><i class="ti ti-ban"></i>
                            {{ __('Exit Admin Login') }}
                        </a>
                    </li>
                    @endImpersonating
                </ul>
            </div>
            <div class="dash-right-drp">
                <ul class="list-unstyled gap-2 header-icon-list">
                    @if (auth()->user() && auth()->user()->type == 'super admin')
                    <li class="web-browse-icon">
                        <a href="{{ url('config-cache') }}" data-bs-toggle="tooltip"
                        title="{{ __('Clear Cache') }}" class="dash-head-link cust-btn h-100  bg-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <path id="_74846e5be5db5b666d3893933be03656"
                                    data-name="74846e5be5db5b666d3893933be03656"
                                    d="M7.719,8.911H8.9V10.1H7.719v1.185H6.539V10.1H5.36V8.911h1.18V7.726h1.18ZM5.36,13.652h1.18v1.185H5.36v1.185H4.18V14.837H3V13.652H4.18V12.467H5.36Zm13.626-2.763H10.138V10.3a1.182,1.182,0,0,1,1.18-1.185h2.36V2h1.77V9.111h2.36a1.182,1.182,0,0,1,1.18,1.185ZM18.4,18H16.044a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,14.755,18H12.5a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,11.216,18H8.958a22.825,22.825,0,0,0,1.163-5.926H18.99A19.124,19.124,0,0,1,18.4,18Z"
                                    transform="translate(-3 -2)" fill="#717580"></path>
                            </svg>
                        </a>
                    </li>
                    @endif
                    @if (auth()->user() && auth()->user()->type == 'admin')
                    <li class="web-browse-icon">
                        <a href="{{ $theme_url ?? '#' }}" target="_blank" data-bs-toggle="tooltip"
                        title="{{ __('Store Link') }}" class="dash-head-link cust-btn h-100  bg-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <path id="_754bac7463b8b1afad8e10a2355d1700"
                                    data-name="754bac7463b8b1afad8e10a2355d1700"
                                    d="M56,48a8,8,0,1,0,8,8A8,8,0,0,0,56,48Zm-.829,14.808a6.858,6.858,0,0,1-4.39-11.256,7.6,7.6,0,0,1,.077.93,2.966,2.966,0,0,0,.382,2.26,3.729,3.729,0,0,1,.362,1.08c.1.341.5.519.77.729.552.423,1.081.916,1.666,1.288.387.246.628.368.515.84a2.98,2.98,0,0,1-.313.951,1.927,1.927,0,0,0,.321.861c.288.288.575.553.889.813C55.938,61.706,55.4,62.229,55.171,62.808Zm5.678-1.959a6.808,6.808,0,0,1-3.56,1.888,2.844,2.844,0,0,1,.842-1.129,2.865,2.865,0,0,0,.757-.937,6.506,6.506,0,0,1,.522-.893c.272-.419-.67-1.051-.975-1.184a10.052,10.052,0,0,1-1.814-1.13c-.435-.306-1.318.16-1.808-.054A9.462,9.462,0,0,1,53,56.166c-.6-.454-.574-.984-.574-1.654.472.017,1.144-.131,1.458.249.1.12.439.655.667.465.186-.155-.138-.779-.2-.925-.193-.451.439-.626.762-.932.422-.4,1.326-1.024,1.254-1.309s-.9-1.1-1.394-.969c-.073.019-.719.7-.844.8q0-.332.01-.663c0-.14-.26-.283-.248-.373.031-.227.664-.64.821-.821-.11-.069-.487-.392-.6-.345-.276.115-.588.194-.863.309a1.756,1.756,0,0,0-.025-.274,6.792,6.792,0,0,1,1.743-.506l.542.218.382.454.382.394.334.108.53-.5L57,49.536v-.321a6.782,6.782,0,0,1,2.9,1.146c-.155.014-.326.037-.518.061a1.723,1.723,0,0,0-.268-.1c.251.54.513,1.073.779,1.606.284.569.915,1.18,1.026,1.781.131.708.04,1.352.111,2.185a3.732,3.732,0,0,0,.9,1.714,1.812,1.812,0,0,0,.707.086A6.815,6.815,0,0,1,60.849,60.849Z"
                                    transform="translate(-48 -48)" fill="#717580"></path>
                            </svg>
                        </a>
                    </li>
                    <li class="web-browse-icon">
                        <a href="{{ route('pos.index') }}" data-bs-toggle="tooltip"
                        title="{{ __('POS') }}" class="dash-head-link cust-btn h-100  bg-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13.79" height="16" viewBox="0 0 13.79 16">
                                <g id="_371925cdd3f531725a9fa8f3ebf8fe9e" data-name="371925cdd3f531725a9fa8f3ebf8fe9e"
                                    transform="translate(-2.26 0)">
                                    <path id="Path_40673" data-name="Path 40673"
                                        d="M10.69,7H3.26a1.025,1.025,0,0,0-1,1V18.45a1.03,1.03,0,0,0,1,1.05h7.43a1.03,1.03,0,0,0,1.03-1.03V8A1.025,1.025,0,0,0,10.69,7ZM4.94,17.86H3.995v-.95H4.94Zm0-2.355H3.995v-.95H4.94Zm0-2.355H3.995V12.2H4.94Zm2.5,4.71H6.5v-.95h.955Zm0-2.355H6.5v-.95h.955Zm0-2.355H6.5V12.2h.955Zm2.5,4.71H8.99v-.95h.95Zm0-2.355H8.99v-.95h.95Zm0-2.355H8.99V12.2h.95Zm.325-3a.17.17,0,0,1-.165.17H3.835a.17.17,0,0,1-.165-.17V8.795a.165.165,0,0,1,.165-.165H10.13a.165.165,0,0,1,.165.165Zm5.09-1.45H15.13v9.09h.25a.67.67,0,0,0,.67-.67V9.375a.67.67,0,0,0-.695-.675Z"
                                        transform="translate(0 -3.5)" fill="#717580"></path>
                                    <rect id="Rectangle_20842" data-name="Rectangle 20842" width="1.465" height="9.095"
                                        transform="translate(12.185 5.2)" fill="#717580"></rect>
                                    <rect id="Rectangle_20843" data-name="Rectangle 20843" width="0.63" height="9.095"
                                        transform="translate(14.06 5.2)" fill="#717580"></rect>
                                    <path id="Path_40674" data-name="Path 40674"
                                        d="M13.895.895a.89.89,0,0,0-.26-.635A.91.91,0,0,0,13,0a.895.895,0,0,0-.91.895v.53h1.79Zm-2.2,0a.76.76,0,0,1,0-.145.68.68,0,0,1,0-.1h.01A.5.5,0,0,1,11.755.5.43.43,0,0,1,11.79.4a1.2,1.2,0,0,1,.145-.26.5.5,0,0,1,.04-.055L12.045,0H7.995A.815.815,0,0,0,7.18.81V3.03h4.5Z"
                                        transform="translate(-2.46)" fill="#717580"></path>
                                </g>
                            </svg>
                        </a>
                    </li>
                    
                    <li class="dropdown quick-add-btn header-quick-add">
                        <a class="btn dash-head-link btn-sm btn-q-add btn-badge h-100 bg-info"
                        title="{{ __('Quick Add') }}" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="ti ti-plus"></i>
                            <span class="ms-2 me-2">{{ __('Quick Add') }}</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('product.create') }}" data-size="lg" data-title="{{ __('Add Product') }}"
                                class="dropdown-item text-wrap"
                                data-bs-placement="top "><span>{{ __('Add New Product') }}</span></a>
                            <a href="#" data-size="md" data-url="{{ route('taxes.create') }}" data-ajax-popup="true"
                                data-title="{{ __('Create Tax') }}" class="dropdown-item text-wrap"
                                data-bs-placement="top "><span>{{ __('Add New Tax') }}</span></a>
                            <a href="#" data-size="md" data-url="{{ route('main-category.create') }}"
                                data-ajax-popup="true" data-title="{{ __('Create Main Category') }}"
                                class="dropdown-item text-wrap"
                                data-bs-placement="top"><span>{{ __('Add New Main Category') }}</span></a>
                            <a href="#" data-size="md" data-url="{{ route('coupon.create') }}" data-ajax-popup="true"
                                data-title="{{ __('Create Coupon') }}" class="dropdown-item text-wrap"
                                data-bs-placement="top "><span>{{ __('Add New Coupon') }}</span></a>
                        </div>
                    </li>
                    
                    <li class="dropdown dash-h-item drp-language">
                        <a href="{{ route('stores.create') }}"
                            class="dropdown-item dash-head-link dropdown-toggle arrow-none h-100 cust-btn bg-primary"
                            data-size="lg">
                            <i class="ti ti-circle-plus"></i>
                            <span class="text-store">{{ __('Create New Store') }}</span>
                        </a>
                    </li>
                    @endif

                    @if (auth()->user() && auth()->user()->type == 'admin')
                    <li class="dash-h-item drp-language menu-lnk has-item">
                        @php
                        $activeStore = getCurrentStore();
                        $store = \App\Models\Store::find($activeStore);
                        $stores = auth()->user()->stores;
                        @endphp
                        <a class="dash-head-link arrow-none me-0 cust-btn h-100 megamenu-btn bg-warning"
                            data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            data-bs-placement="bottom" data-bs-original-title="Select Store">
                            <i class="ti ti-building-store"></i>
                            <span class="hide-mob">{{ ucfirst($store->name ?? '') }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                            <input type="text" id="searchInput" class="form-control mb-2"
                                placeholder="{{ __('Search...') }}">
                            <div id="storeList" style="max-height: 200px; overflow-y: auto;">
                                @if (auth()->user()->type == 'admin')
                                @foreach ($stores as $store)
                                @if ($store->is_active)
                                <a href="@if ($activeStore == $store->id) # @else {{ route('change.store', $store->id) }} @endif"
                                    class="dropdown-item">
                                    @if ($activeStore == $store->id)
                                    <i class="ti ti-checks text-primary"></i>
                                    @endif
                                    {{ ucfirst($store->name) }}
                                </a>
                                @else
                                <a href="#!" class="dropdown-item">
                                    <i class="ti ti-lock"></i>
                                    <span>{{ $store->name }}</span>
                                    @if (isset(auth()->user()->type))
                                    @if (auth()->user()->type == 'admin')
                                    <span class="badge bg-dark">{{ __(auth()->user()->type) }}</span>
                                    @else
                                    <span class="badge bg-dark">{{ __('Shared') }}</span>
                                    @endif
                                    @endif
                                </a>
                                @endif
                                @endforeach
                                @else
                                @foreach ($user->stores as $store)
                                @if ($store->is_active)
                                <a href="#" class="dropdown-item">
                                    @if ($activeStore == $store->id)
                                    <i class="ti ti-checks text-primary"></i>
                                    @endif
                                    {{ ucfirst($store->name) }}
                                </a>
                                @endif
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </li>
                    @endif

                    <li class="dropdown dash-h-item drp-language">
                        <a class="dash-head-link dropdown-toggle  arrow-none me-0 bg-info h-100" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="ti ti-world nocolor"></i>
                            <span class="mx-1">{{ Str::upper($currentLanguage) }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>

                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                            @foreach ($displaylang as $key => $lang)
                            @if(isset($setting['disable_lang']) && str_contains($setting['disable_lang'], $key))
                            @unset($key)
                            @continue
                            @endif
                            <a href="{{ route('change.language', $key) }}"
                                class="dropdown-item {{ $currentLanguage == $key ? 'text-primary' : '' }}">
                                <span>{{ Str::ucfirst($lang) }}</span>
                            </a>
                            @endforeach
                            @if (auth()->user() && auth()->user()->type == 'super admin')
                            <a href="{{ route('manage.language', [auth()->user()->language]) }}"
                                class="dropdown-item border-top py-1 text-primary">{{ __('Manage Languages') }}
                            </a>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>