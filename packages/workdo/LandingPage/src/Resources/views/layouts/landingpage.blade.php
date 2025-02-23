@php
    $settings = \Workdo\LandingPage\Entities\LandingPageSetting::settings();
    $logo = get_file('storage/uploads/landing_page_image', 'grocery');

    $sup_logo = get_file('storage/uploads/logo', 'grocery');
    $superadmin = \App\Models\User::where('type', 'super admin')->first();
    $setting = getSuperAdminAllSetting();
    $SITE_RTL = $setting['SITE_RTL'] ?? 'off';

    // $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if (!isset($setting['color'])) {
        $color = 'theme-3';
    } elseif (isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $color = 'custom-color';
    } else {
        if (
            !in_array($setting['color'], [
                'theme-1',
                'theme-2',
                'theme-3',
                'theme-4',
                'theme-5',
                'theme-6',
                'theme-7',
                'theme-8',
                'theme-9',
                'theme-10',
            ])
        ) {
            $color = 'custom-color' ?? 'theme-3';
        } else {
            $color = $setting['color'] ?? 'theme-3';
        }
    }
    $menusettings = \Workdo\LandingPage\Entities\OwnerMenuSetting::where('created_by', $superadmin->id)->first();

    if (isset($menusettings) && $menusettings->menus_id) {
        $topNavItems = \Workdo\LandingPage\Entities\OwnerMenuSetting::get_ownernav_menu($menusettings->menus_id);
    }
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on' ? 'rtl' : '' }}">

<head>
    <title>
        {{ \App\Models\Utility::GetValueByName('title_text', APP_THEME(), 1) ? \App\Models\Utility::GetValueByName('title_text', APP_THEME(), 1) : (env('APP_NAME') ?? 'Ecommercego saas') }}
    </title>
    <!-- Meta -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="WorkDo.io" />
    <meta name="base-url" content="{{ URL::to('/') }}">

    <meta name="title" content="{{ isset($settings['metatitle']) ? $settings['metatitle'] : 'EcommerceGo' }}">
    <meta name="keywords" content="{{ isset($settings['metakeyword']) ? $settings['metakeyword'] : 'EcommerceGo, Store with Multi theme and Multi Store' }}">
    <meta name="description" content="{{ isset($settings['metadesc']) ? $settings['metadesc'] : 'Discover the efficiency of EcommerceGo, a user-friendly web application by Workdo.io.'}}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ isset($settings['metatitle']) ? $settings['metatitle'] : 'EcommerceGo' }}">
    <meta property="og:description" content="{{ isset($settings['metadesc']) ? $settings['metadesc'] : 'Discover the efficiency of EcommerceGo, a user-friendly web application by Workdo.io.'}} ">
    <meta property="og:image" content="{{ get_file(isset($settings['metaimage']) ? $settings['metaimage'] : 'storage/uploads/ecommercego-saas-preview.png')  }}{{'?'.time() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ isset($settings['metatitle']) ? $settings['metatitle'] : 'EcommerceGo' }}">
    <meta property="twitter:description" content="{{ isset($settings['metadesc']) ? $settings['metadesc'] : 'Discover the efficiency of EcommerceGo, a user-friendly web application by Workdo.io.'}} ">
    <meta property="twitter:image" content="{{ get_file(isset($settings['metaimage']) ? $settings['metaimage'] : 'storage/uploads/ecommercego-saas-preview.png')  }}{{'?'.time() }}">

    <!-- Favicon icon -->
    <link rel="icon" href="{{ get_file($setting['favicon'] . '?timestamp=' . time(), 'grocery') }}"
        type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/fonts/material.css') }}" />

    <!-- vendor css -->
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/customizer.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/landing-page.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/assets/swiper/dist/css/swiper-bundle.min.css') }}" />

    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/style-rtl.css') }}" />
    @elseif (isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/style-dark.css') }}" />
    @else
        <link rel="stylesheet" href="{{ url('packages/workdo/LandingPage/src/Resources/assets/css/style.css') }}"
            id="main-style-link" />
    @endif

    <link rel="stylesheet" href="{{ url('css/custom-color.css') }}" />
    <!-- notification css -->
    <link rel="stylesheet" href="{{ url('assets/css/plugins/notifier.css') }}" />

    <style>
        :root {
            --color-customColor: <?=$setting['color'] ?? 'linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #ffffff 99.86%)' ?>;
        }
    </style>
</head>
@if (isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on')

    <body class="{{ $color }} landing-dark landing-page">
    @else

        <body class="{{ $color }} landing-page">
@endif
<!-- [ Header ] start -->
<header class="main-header">
    @if (isset($settings['topbar_status']) && $settings['topbar_status'] == 'on')
        <div class="announcement bg-dark text-center p-2">
            <p class="mb-0">{!! $settings['topbar_notification_msg'] !!}</p>
        </div>
    @endif
    @if (isset($settings['menubar_status']) && $settings['menubar_status'] == 'on')
        <div class="container">
            <nav class="navbar navbar-expand-md  default top-nav-collapse">
                <div class="header-left custom-header-logo">
                    <a class="navbar-brand bg-transparent logo" href="{{ \URL::to('/') }}">
                        <img src="{{ file_exists($settings['site_logo']) ? get_file($settings['site_logo']) . '?timestamp=' . time() : $logo . '/' . $settings['site_logo'] . '?timestamp=' . time() }}" class="logo"
                            alt="logo">
                    </a>
                </div>
                @if (isset($menusettings) &&
                        isset($menusettings->menus_id) &&
                        $menusettings->enable_header == 'on' &&
                        !empty($topNavItems))
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                        <ul class="lnding-menubar p-0 m-0">
                            @foreach ($topNavItems as $navGroup)
                                <li class="menu-lnk has-item">
                                    <a class="dash-head-link" href="#">
                                        <span>
                                            {{ $navGroup['name'] }}
                                        </span>
                                        <i class="ti ti-chevron-down drp-arrow"></i>
                                    </a>
                                    <div class="menu-dropdown">
                                        <ul class="p-0 m-0">
                                            @foreach ($navGroup['items'] as $nav)
                                                @if ($nav->type == 'page')
                                                    <li class="lnk-itm">
                                                        <a href="{{ url('landing-pages' . '/' . $nav->slug) }}"
                                                            target="{{ $nav->target }}" class="dropdown-item">
                                                            <span>{{ $nav->title }}</span>
                                                        </a>
                                                        @if (!empty($nav->children) && isset($nav->children))
                                                            <ul class="lnk-child">
                                                                @foreach ($nav->children[0] as $child)
                                                                    @if (!empty($child))
                                                                        <li>
                                                                            @if ($child->type == 'page')
                                                                                <a href="{{ url('landing-pages' . '/' . $child->slug) }}"
                                                                                    target="{{ $child->target }}"
                                                                                    class="dropdown-item">
                                                                                    <span>{{ $child->title }}</span>
                                                                                </a>
                                                                            @else
                                                                                <a href="{{ $child->slug }}"
                                                                                target="{{ $child->target }}"
                                                                                class="dropdown-item">
                                                                                    <span>{{ $child->title }}</span>
                                                                                </a>
                                                                            @endif
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @else
                                                    <li>
                                                        <a href="{{ $nav->slug }}" target="{{ $nav->target }}"
                                                            class="dropdown-item">
                                                            <span>{{ $nav->title }}</span>
                                                        </a>
                                                        @if (!empty($nav->children))
                                                            <ul>
                                                                @foreach ($nav->children[0] as $child)
                                                                    @if (!empty($child))
                                                                        <li>
                                                                            @if ($child->type == 'page')
                                                                                <a href="{{ url('landing-pages' . '/' . $child->slug) }}"
                                                                                    target="{{ $child->target }}"
                                                                                    class="dropdown-item">
                                                                                    <span>{{ $child->title }}</span>
                                                                                </a>
                                                                            @else
                                                                                <a href="{{ $child->slug }}"
                                                                                target="{{ $child->target }}"
                                                                                class="dropdown-item">
                                                                                    <span>{{ $child->title }}</span>
                                                                                </a>
                                                                            @endif
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <button class="navbar-toggler bg-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                @endif
                <div class="ms-auto d-flex justify-content-end gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-dark rounded"><span
                            class="hide-mob me-2">{{ __('Login') }}</span> <i data-feather="log-in"></i></a>
                    @if ($setting['SIGNUP'] == 'on')
                        <a href="{{ route('register') }}" class="btn btn-outline-dark rounded"><span
                                class="hide-mob me-2">{{ __('Register') }}</span> <i data-feather="user-check"></i></a>
                    @endif
                    <button class="navbar-toggler " type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </nav>
        </div>
    @endif

</header>
<!-- [ Header ] End -->
<!-- [ Banner ] start -->
@if (isset($settings['home_status']) && $settings['home_status'] == 'on')
    <section class="main-banner bg-primary" id="home">
        <div class="container-offset">
            <div class="row gy-3 g-0 align-items-center">
                <div class="col-xxl-4 col-md-6">
                    <span class="badge py-2 px-3 bg-white text-dark rounded-pill fw-bold mb-3">
                        {{ $settings['home_offer_text'] }}</span>
                    <h1 class="mb-3">
                        {{ $settings['home_heading'] }}
                    </h1>
                    <h6 class="mb-0">{{ $settings['home_description'] }}</h6>
                    <div class="d-flex gap-3 mt-4 banner-btn">
                        @if ($settings['home_live_demo_link'])
                            <a href="{{ $settings['home_live_demo_link'] }}"
                                class="btn btn-outline-dark">{{ __('Live Demo') }}
                                <i data-feather="play-circle" class="ms-2"></i></a>
                        @endif
                        @if ($settings['home_buy_now_link'])
                            <a href="{{ $settings['home_buy_now_link'] }}"
                                class="btn btn-outline-dark">{{ __('Buy Now') }} <i data-feather="lock"
                                    class="ms-2"></i></a>
                        @endif
                    </div>
                </div>

                <div class="col-xxl-8 col-md-6">
                    <div class="dash-preview">
                        <img class="img-fluid preview-img" src="{{ file_exists($settings['home_banner']) ? get_file($settings['home_banner']) : $logo . '/' . $settings['home_banner'] }}"
                            alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row g-0 gy-2 mt-4 align-items-center">
                <div class="col-xxl-3">
                    <p class="mb-0">{{ __('Trusted by') }} <b
                            class="fw-bold">{{ $settings['home_trusted_by'] }}</b></p>
                </div>
                <div class="col-xxl-9">
                    <div class="row gy-3 row-cols-9">
                        @foreach (explode(',', $settings['home_logo']) as $k => $home_logo)
                            <div class="col-auto custom-header-logo">
                                <img src="{{ file_exists($home_logo) ? get_file($home_logo) : $logo . '/' . $home_logo }}" alt="" class="img-fluid"
                                    style="width: 130px;">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ Banner ] start -->
<!-- [ features ] start -->
@if (isset($settings['feature_status']) && $settings['feature_status'] == 'on')
    <section class="features-section section-gap bg-dark" id="features">
        <div class="container">
            <div class="row gy-3">
                <div class="col-xxl-4">
                    <span class="d-block mb-2 text-uppercase text-white">{{ $settings['feature_title'] }}</span>
                    <div class="title mb-4">
                        <h2><b class="fw-bold">{!! $settings['feature_heading'] !!}</b></h2>
                    </div>
                    <p class="mb-3">{!! $settings['feature_description'] !!}</p>
                    @if ($settings['feature_buy_now_link'])
                        <a href="{{ $settings['feature_buy_now_link'] }}"
                            class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now') }}
                            <i data-feather="lock" class="ms-2"></i></a>
                    @endif
                </div>
                <div class="col-xxl-8">
                    <div class="row">
                        @if (is_array(json_decode($settings['feature_of_features'], true)) ||
                                is_object(json_decode($settings['feature_of_features'], true)))
                            @foreach (json_decode($settings['feature_of_features'], true) as $key => $value)
                                <div class="col-lg-4 col-sm-6 d-flex">
                                    <div class="card {{ $key == 0 ? 'bg-primary' : '' }}">
                                        <div class="card-body">
                                            <span class="theme-avtar avtar avtar-xl mb-4">
                                                <img src="{{ file_exists($value['feature_logo']) ? get_file($value['feature_logo']) : $logo . '/' . $value['feature_logo'] }}" alt="">
                                            </span>
                                            <h3 class="mb-3 {{ $key == 0 ? '' : 'text-white' }}">
                                                {!! $value['feature_heading'] !!}</h3>
                                            <p class=" f-w-600 mb-0 {{ $key == 0 ? 'text-body' : '' }}">
                                                {!! strip_tags($value['feature_description']) !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="mt-5">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 text-uppercase text-white">{{ $settings['feature_title'] }}</span>
                        <h2 class="mb-4">{!! $settings['highlight_feature_heading'] !!}</h2>
                        <p>{!! $settings['highlight_feature_description'] !!}</p>
                    </div>
                    <div class="features-preview">
                        <img class="img-fluid m-auto d-block"
                            src="{{ file_exists($settings['highlight_feature_image']) ? get_file($settings['highlight_feature_image']) : $logo . '/' . $settings['highlight_feature_image'] }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ features ] start -->
<!-- [ element ] start -->
@if (isset($settings['feature_status']) && $settings['feature_status'] == 'on')
    <section class="element-section  section-gap ">
        <div class="container">
            @if (is_array(json_decode($settings['other_features'], true)) ||
                    is_object(json_decode($settings['other_features'], true)))
                @foreach (json_decode($settings['other_features'], true) as $key => $value)
                    @if ($key % 2 == 0)
                        <div class="row align-items-center justify-content-center mb-4">
                            <div class="col-lg-4 col-md-6">
                                <div class="title mb-4">
                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                    <h2>
                                        {!! $value['other_features_heading'] !!}
                                    </h2>
                                </div>
                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                @if ($value['other_feature_buy_now_link'])
                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now') }}
                                    <i data-feather="lock" class="ms-2"></i></a>
                                @endif
                            </div>
                            <div class="col-lg-7 col-md-6 res-img">
                                <div class="img-wrapper">
                                    <img src="{{ file_exists($value['other_features_image']) ? get_file($value['other_features_image']) : $logo . '/' . $value['other_features_image'] }}" alt=""
                                        class="img-fluid header-img">
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row align-items-center justify-content-center mb-4">
                            <div class="col-lg-7 col-md-6 m-bottom-img">
                                <div class="img-wrapper">
                                    <img src="{{ file_exists($value['other_features_image']) ? get_file($value['other_features_image']) : $logo . '/' . $value['other_features_image'] }}" alt=""
                                        class="img-fluid header-img">
                                </div>
                            </div>
                            <div class="col-lg-4  col-md-6">
                                <div class="title mb-4">
                                    <span class="d-block fw-bold mb-2 text-uppercase">{{ __('Features') }}</span>
                                    <h2>
                                        {!! $value['other_features_heading'] !!}
                                    </h2>
                                </div>
                                <p class="mb-3">{!! $value['other_featured_description'] !!}</p>
                                @if ($value['other_feature_buy_now_link'])
                                <a href="{{ $value['other_feature_buy_now_link'] }}"
                                    class="btn btn-primary rounded-pill d-inline-flex align-items-center">{{ __('Buy Now') }}
                                    <i data-feather="lock" class="ms-2"></i></a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        </div>
    </section>
@endif

<!-- [ element ] end -->
@stack('campaignsPage')
<!-- [ element ] start -->
@if (isset($settings['discover_status']) && $settings['discover_status'] == 'on')
    <section class="bg-dark section-gap">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 text-uppercase text-white">{{ __('DISCOVER') }}</span>
                        <h2 class="mb-4">{!! $settings['discover_heading'] !!}</h2>
                        <p>{!! $settings['discover_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @if (is_array(json_decode($settings['discover_of_features'], true)) ||
                        is_object(json_decode($settings['discover_of_features'], true)))
                    @foreach (json_decode($settings['discover_of_features'], true) as $key => $value)
                        <div class="col-xxl-3 col-sm-6 col-lg-4 ">
                            <div class="card   border {{ $key == 1 ? 'bg-primary' : 'bg-transparent' }}">
                                <div class="card-body text-center">
                                    <span class="theme-avtar avtar avtar-xl mx-auto mb-4">
                                        <img src="{{ file_exists($value['discover_logo']) ? get_file($value['discover_logo']) : $logo . '/' . $value['discover_logo'] }}" alt="">
                                    </span>
                                    <h3 class="mb-3 {{ $key == 1 ? '' : 'text-white' }} ">{!! $value['discover_heading'] !!}
                                    </h3>
                                    <p class="{{ $key == 1 ? 'text-body' : '' }}">
                                        {!! strip_tags($value['discover_description']) !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
            <div class="d-flex flex-column justify-content-center flex-sm-row gap-3 mt-3">
                @if (isset($settings['discover_live_demo_link']) && $settings['discover_live_demo_link'])
                    <a href="{{ $settings['discover_live_demo_link'] }}"
                        class="btn btn-outline-light rounded-pill">{{ __('Live
                                                                                                                                                                        Demo') }}
                        <i data-feather="play-circle" class="ms-2"></i> </a>
                @endif

                @if (isset($settings['discover_buy_now_link']) && $settings['discover_buy_now_link'])
                    <a href="{{ $settings['discover_buy_now_link'] }}"
                        class="btn btn-primary rounded-pill">{{ __('Buy Now') }}
                        <i data-feather="lock" class="ms-2"></i> </a>
                @endif
            </div>
        </div>
    </section>
@endif
<!-- [ element ] end -->
<!-- [ Screenshots ] start -->
@if (isset($settings['screenshots_status']) && $settings['screenshots_status'] == 'on')
    <section class="screenshots section-gap">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('SCREENSHOTS') }}</span>
                        <h2 class="mb-4">{!! $settings['screenshots_heading'] !!}</h2>
                        <p>{!! $settings['screenshots_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row gy-4 gx-4">
                @if (isset($settings['screenshots']) &&
                        (is_array(json_decode($settings['screenshots'], true)) ||
                            is_object(json_decode($settings['screenshots'], true))))
                    @foreach (json_decode($settings['screenshots'], true) as $value)
                        <div class="col-md-4 col-sm-6">
                            <div class="screenshot-card">
                                <div class="img-wrapper">
                                    <img src="{{ file_exists($value['screenshots']) ? get_file($value['screenshots']) : $logo . '/' . $value['screenshots'] }}"
                                        class="img-fluid header-img mb-4 shadow-sm" alt="">
                                </div>
                                <h5 class="mb-0">{!! $value['screenshots_heading'] !!}</h5>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endif
<!-- [ Screenshots ] start -->
<!-- [ subscription ] start -->
@if (isset($settings['plan_status']) && $settings['plan_status'])
    <section class="subscription bg-primary section-gap" id="plan">
        <div class="container">
            <div class="row mb-2 justify-content-center">
                <div class="col-xxl-6">
                    <div class="title text-center mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('PLAN') }}</span>
                        <h2 class="mb-4">{!! $settings['plan_heading'] !!}</h2>
                        <p>{!! $settings['plan_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                @php
                    $collection = \App\Models\Plan::where('is_disable', 1)->orderBy('price', 'asc')->get();
                @endphp
                @foreach ($collection as $key => $value)
                    <div class="col-xxl-3 col-lg-4 col-md-6 mb-5">
                        <div class="card price-card shadow-none {{ $key == 2 ? 'bg-dark' : '' }}"
                            style="{{ $key == 2 ? 'color:white !important' : '' }}"> {{-- {{ $key == 1 ? 'bg-light-primary' : '' }} --}}
                            <div class="card-body">
                                <span class="price-badge bg-dark">{{ $value->name }}</span>
                                <span
                                    class="mb-4 f-w-600 p-price">{{ trim(default_currency_format_with_sym($value->price)) }}<small
                                        class="text-sm">/{{ $value->duration }}</small></span>
                                @if ($value->trial == '1')
                                    <p class="mb-0">{{ __('Free Trial Days : ') }}<b>{{ $value->trial_days }}</b>
                                    </p>
                                @endif
                                <p>
                                    {!! $value->description !!}
                                </p>
                                <ul class="list-unstyled my-3">
                                    <li>
                                        <div class="form-check text-start">
                                            <label class="form-check-label"
                                                for="customCheckc1">{{ $value->max_stores != -1 ? $value->max_stores . ' Store' : 'Unlimited' }}
                                                {{__('Store')}}</label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-check text-start">
                                            <label class="form-check-label" for="customCheckc1">
                                                {{ $value->max_products != -1 ? $value->max_products . ' Store' : 'Unlimited' }}
                                                {{__('Products')}}</label>
                                        </div>
                                    </li>

                                    @if ($value->enable_domain == 'on')
                                        <li>
                                            <div class="form-check text-start">
                                                <label class="form-check-label"
                                                    for="customCheckc1">{{ $value->enable_domain == 'on' ? 'Enable Custom Domain' : '' }}</label>
                                            </div>
                                        </li>
                                    @endif
                                    @if ($value->enable_subdomain == 'on')
                                    <li>
                                        <div class="form-check text-start">
                                            <label class="form-check-label"
                                                for="customCheckc1">{{ $value->enable_subdomain == 'on' ? 'Enable Sub Domain' : '' }}</label>
                                        </div>
                                    </li>
                                    @endif
                                    @if ($value->pwa_store == 'on')
                                    <li>
                                        <div class="form-check text-start">
                                            <label class="form-check-label"
                                                for="customCheckc1">{{ $value->pwa_store == 'on' ? 'Enable Progressive Web App (PWA)' : '' }}</label>
                                        </div>
                                    </li>
                                    @endif
                                    @if ($value->enable_chatgpt == 'on')
                                    <li>
                                        <div class="form-check text-start">
                                            <label class="form-check-label"
                                                for="customCheckc1">{{ $value->enable_chatgpt == 'on' ? 'Enable Chatgpt' : '' }}</label>
                                        </div>
                                    </li>
                                    @endif

                                </ul>
                                @if ($setting['SIGNUP'] == 'on')
                                    <div class="d-grid">
                                        <a href="{{ route('register', ['plan_id' => \Illuminate\Support\Facades\Crypt::encrypt($value->id)]) }}" class="btn btn-primary rounded-pill">{{__('Start with Starter')}} <i data-feather="log-in" class="ms-2"></i> </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
@endif
<!-- [ subscription ] end -->
<!-- [ FAqs ] start -->

@if (isset($settings['faq_status']) && $settings['faq_status'] == 'on')
    <section class="faqs section-gap bg-gray-100" id="faq">
        <div class="container">
            <div class="row mb-2">
                <div class="col-xxl-6">
                    <div class="title mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ $settings['faq_title'] }}</span>
                        <h2 class="mb-4">{!! $settings['faq_heading'] !!}</h2>
                        <p>{!! $settings['faq_description'] !!}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        @if (isset($settings['faqs']) &&
                                (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true))))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                @if ($key % 2 == 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                {!! $value['faq_questions'] !!}
                                            </button>
                                        </h2>
                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                {!! $value['faq_answer'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="accordion accordion-flush" id="accordionFlushExample2">
                        @if (is_array(json_decode($settings['faqs'], true)) || is_object(json_decode($settings['faqs'], true)))
                            @foreach (json_decode($settings['faqs'], true) as $key => $value)
                                @if ($key % 2 != 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ 'flush-heading' . $key }}">
                                            <button class="accordion-button collapsed fw-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="{{ '#flush-' . $key }}"
                                                aria-expanded="false" aria-controls="{{ 'flush-collapse' . $key }}">
                                                {!! $value['faq_questions'] !!}
                                            </button>
                                        </h2>
                                        <div id="{{ 'flush-' . $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="{{ 'flush-heading' . $key }}"
                                            data-bs-parent="#accordionFlushExample2">
                                            <div class="accordion-body">
                                                {!! $value['faq_answer'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>
@endif
<!-- [ FAqs ] end -->
<!-- [ testimonial ] start -->
@if (isset($settings['testimonials_status']) && $settings['testimonials_status'] == 'on')
    <section class="testimonial section-gap">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <div class="title mb-4">
                        <span class="d-block mb-2 fw-bold text-uppercase">{{ __('TESTIMONIALS') }}</span>
                        <h2 class="mb-2">{!! $settings['testimonials_heading'] !!}</h2>
                        <p>{!! $settings['testimonials_description'] !!}</p>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row justify-content-center gy-3">
                        @if (isset($settings['testimonials']) &&
                                (is_array(json_decode($settings['testimonials'])) || is_object(json_decode($settings['testimonials']))))
                            @foreach (json_decode($settings['testimonials']) as $key => $value)
                                <div class="col-xxl-4 col-sm-6 col-lg-6 col-md-4">
                                    <div class="card bg-dark shadow-none mb-0">
                                        <div class="card-body p-3">
                                            <div class="d-flex mb-3 align-items-center justify-content-between">
                                                <span class="theme-avtar avtar avtar-sm bg-light-dark rounded-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="36"
                                                        height="23" viewBox="0 0 36 23" fill="none">
                                                        <path
                                                            d="M12.4728 22.6171H0.770508L10.6797 0.15625H18.2296L12.4728 22.6171ZM29.46 22.6171H17.7577L27.6669 0.15625H35.2168L29.46 22.6171Z"
                                                            fill="white" />
                                                    </svg>
                                                </span>
                                                <span style="color: white;">
                                                    @for ($i = 1; $i <= (int) $value->testimonials_star; $i++)
                                                        <i data-feather="star"></i>
                                                    @endfor
                                                </span>
                                            </div>
                                            <h3 class="text-white">{{ $value->testimonials_title }}</h3>
                                            <p class="hljs-comment">
                                                {{ $value->testimonials_description }}
                                            </p>
                                            <div class="d-flex gap-3 align-items-center ">
                                                <img src="{{ file_exists($value->testimonials_user_avtar) ? get_file($value->testimonials_user_avtar) : $logo . '/' . $value->testimonials_user_avtar }}"
                                                    class="wid-40 rounded-circle" alt="">
                                                <span class="text-white">
                                                    <b class="fw-bold d-block">{{ $value->testimonials_user }}</b>
                                                    {{ $value->testimonials_designation }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <p class="mb-0 f-w-600">
                        {!! $settings['testimonials_long_description'] !!}
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- [ testimonial ] end -->
<!-- [ Footer ] start -->
<footer class="site-footer bg-gray-100">
    <div class="container">
        <div class="footer-row">
            <div class="ftr-col cmp-detail">
                <div class="footer-logo mb-3">
                    <a href="#">
                        <img src="{{ file_exists($settings['site_logo']) ? get_file($settings['site_logo']) . '?timestamp=' . time() : $logo . '/' . $settings['site_logo'] . '?timestamp=' . time() }}"
                            alt="logo">
                    </a>
                </div>
                <p>
                    {!! $settings['site_description'] !!}
                </p>

            </div>
            @if (isset($menusettings) && isset($menusettings->menus_id) && $menusettings->enable_footer == 'on')
                @foreach ($topNavItems as $navGroup)
                    <div class="ftr-col">
                        <ul class="list-unstyled">
                            @foreach ($navGroup['items'] as $nav)
                                @if ($nav->type == 'page')
                                    <li>
                                        <a href="{{ url('landing-pages' . '/' . $nav->slug) }}"
                                            target="{{ $nav->target }}">{{ $nav->title }}</a>
                                        @if (!empty($nav->children) && isset($nav->children))
                                            <ul class="lnk-child">
                                                @foreach ($nav->children[0] as $child)
                                                    @if (!empty($child))
                                                        <li>
                                                            @if ($child->type == 'page')
                                                                <a href="{{ url('landing-pages' . '/' . $child->slug) }}"
                                                                    target="{{ $child->target }}"
                                                                    class="dropdown-item">
                                                                    <span>{{ $child->title }}</span>
                                                                </a>
                                                            @else
                                                                <a href="{{ $child->slug }}"
                                                                target="{{ $child->target }}"
                                                                class="dropdown-item">
                                                                    <span>{{ $child->title }}</span>
                                                                </a>
                                                            @endif
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $nav->slug }}"
                                            target="{{ $nav->target }}">{{ $nav->title }}</a>
                                        @if (!empty($nav->children) && isset($nav->children))
                                            <ul class="lnk-child">
                                                @foreach ($nav->children[0] as $child)
                                                    @if (!empty($child))
                                                        <li>
                                                            @if ($child->type == 'page')
                                                                <a href="{{ url('landing-pages' . '/' . $child->slug) }}"
                                                                    target="{{ $child->target }}"
                                                                    class="dropdown-item">
                                                                    <span>{{ $child->title }}</span>
                                                                </a>
                                                            @else
                                                                <a href="{{ $child->slug }}"
                                                                target="{{ $child->target }}"
                                                                class="dropdown-item">
                                                                    <span>{{ $child->title }}</span>
                                                                </a>
                                                            @endif
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
            @if ($settings['joinus_status'] == 'on')
                <div class="ftr-col ftr-subscribe">
                    <h2>{!! $settings['joinus_heading'] !!}</h2>
                    <p>{!! $settings['joinus_description'] !!}</p>
                    <form method="post" action="{{ route('join_us_store') }}">
                        @csrf
                        <div class="input-wrapper border border-dark">
                            <input type="text" name="email" placeholder="Type your email address...">
                            <button type="submit" class="btn btn-dark rounded-pill">{{ __('Join Us!') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
    <div class="border-top border-dark text-center p-2">
        {{-- <p class="mb-0">
                Copyright © 2022 | Design By ERPGo
            </p> --}}

        <p class="mb-0">
            @if (isset($setting['footer_text']) &&
                    (strpos($setting['footer_text'], '©') === false && strpos($setting['footer_text'], '&copy;') === false))
                &copy;
            @endif

            {{ date('Y') }}
            {{ isset($setting['footer_text']) ? $setting['footer_text'] : config('app.name', 'E-CommerceGo') }}
        </p>


    </div>
</footer>
@if (isset($setting['enable_cookie']) && $setting['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif
<!-- [ Footer ] end -->
<!-- Required Js -->

<script src="{{ url('js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ url('packages/workdo/LandingPage/src/Resources/assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ url('packages/workdo/LandingPage/src/Resources/assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ url('packages/workdo/LandingPage/src/Resources/assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ url('packages/workdo/LandingPage/src/Resources/assets/js/plugins/notifier.js') }}"></script>
<script src="{{ url('packages/workdo/LandingPage/src/Resources/assets/assets/swiper/dist/js/swiper-bundle.min.js') }}"></script>
<script src="{{ url('js/custom.js') }}{{ '?' . time() }}"></script>
<script>
    // Start [ Menu hide/show on scroll ]
    let ost = 0;
    document.addEventListener("scroll", function() {
        let cOst = document.documentElement.scrollTop;
        if (cOst == 0) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
        } else if (cOst > ost) {
            document.querySelector(".navbar").classList.add("top-nav-collapse");
            document.querySelector(".navbar").classList.remove("default");
        } else {
            document.querySelector(".navbar").classList.add("default");
            document
                .querySelector(".navbar")
                .classList.remove("top-nav-collapse");
        }
        ost = cOst;
    });
    // End [ Menu hide/show on scroll ]

    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: "#navbar-example",
    });
    feather.replace();
</script>
<script>
    // Function to show or hide navigation arrows based on the number of slides
    function toggleNavigationArrows(swiper) {
    const slideCount = swiper.slides.length;
    const nextEl = document.querySelector('.swiper-button-next');
    const prevEl = document.querySelector('.swiper-button-prev');
    if (slideCount <= swiper.params.slidesPerView) {
    nextEl.style.display = 'none';
    prevEl.style.display = 'none';
    } else {
    nextEl.style.display = '';
    prevEl.style.display = '';
    }
    }

    // Initialize Swiper
    var swiper = new Swiper('.campaign-slider', {
    spaceBetween: 15,
    mousewheel: false,
    keyboard: {
    enabled: true
    },
    breakpoints: {
    1199: {
    slidesPerView: 4,
    },
    991: {
    slidesPerView: 3,
    },
    768: {
    slidesPerView: 2,
    },
    0: {
    slidesPerView: 1,
    }
    },
    navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev"
    },
    on: {
    init: function () {
    toggleNavigationArrows(this);
    },
    resize: function () {
    toggleNavigationArrows(this);
    }
    }
    });

    // Initialize Feather icons
    feather.replace();
</script>

@if ($message = Session::get('success'))

    <script>
        var site_url = $('meta[name="base-url"]').attr('content');
        show_toastr('{{ __('Success') }}', '{!! $message !!}', 'success')
    </script>
@endif

@if ($message = Session::get('error'))
    <script>
        var site_url = $('meta[name="base-url"]').attr('content');
        show_toastr('{{ __('Error') }}', '{!! $message !!}', 'error')
    </script>
@endif
</body>

</html>
