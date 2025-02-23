@extends('front_end.layouts.app')
@section('page-title')
    {{ __('Products') }}
@endsection
@php

@endphp

@section('content')
    @include('front_end.sections.partision.header_section')

        <section class="product-page-first-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-12 pdp-center-column">
                        <div class="pdp-center-inner-sliders">
                            <div class="main-slider-wrp">
                                <div class="pdp-main-slider">
                                    @if (!empty($product->Sub_image($product->id)['data']))
                                        @foreach ($product->Sub_image($product->id)['data'] as $item)
                                            <div class="pdp-main-slider-itm">
                                                <div class="pdp-main-img">
                                                    <img src='{{ get_file($item->image_path, $currentTheme) }}'>

                                                    @foreach ($latestSales as $productId => $saleData)
                                                        <div class="custom-output sale-tag-product">
                                                            <div class="sale_tag_icon rounded col-1 onsale">
                                                                <div>{{ __('Sale!') }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="pdp-main-slider-itm">
                                            <div class="pdp-main-img">
                                                <img src='{{ get_file($product->cover_image_path, $currentTheme) }}'>
                                                @foreach ($latestSales as $productId => $saleData)
                                                    <div class="custom-output sale-tag-product">
                                                        <div class="sale_tag_icon rounded col-1 onsale">
                                                            <div>{{ __('Sale!') }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="pdp-thumb-wrap">
                                <div class="pdp-thumb-slider common-arrows">
                                    @foreach ($product->Sub_image($product->id)['data'] as $item)
                                        <div class="pdp-thumb-slider-itm">
                                            <div class="pdp-thumb-img">
                                                <img src="{{ get_file($item->image_path, $currentTheme) }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="pdp-thumb-nav-wrap">
                                    <div class="pdp-thumb-nav">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 pdp-left-column">
                        <div class="pdp-left-column-inner">
                            <a href="{{ route('page.product-list', $slug) }}" class="back-btn">
                                <span class="svg-ic">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="5" viewBox="0 0 11 5"
                                        fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M10.5791 2.28954C10.5791 2.53299 10.3818 2.73035 10.1383 2.73035L1.52698 2.73048L2.5628 3.73673C2.73742 3.90636 2.74146 4.18544 2.57183 4.36005C2.40219 4.53467 2.12312 4.53871 1.9485 4.36908L0.133482 2.60587C0.0480403 2.52287 -0.000171489 2.40882 -0.000171488 2.2897C-0.000171486 2.17058 0.0480403 2.05653 0.133482 1.97353L1.9485 0.210321C2.12312 0.0406877 2.40219 0.044729 2.57183 0.219347C2.74146 0.393966 2.73742 0.673036 2.5628 0.842669L1.52702 1.84888L10.1383 1.84875C10.3817 1.84874 10.5791 2.04609 10.5791 2.28954Z"
                                            fill="white"></path>
                                    </svg>
                                </span>
                                {{ $page_json->product_page->section->button->text ?? __('Back to Category') }}
                            </a>
                            <div class="product-description">
                                <div class="pdp-top-info d-flex align-items-center">
                                    <p>{{ ucfirst($product->label->name ?? '') }}</p>
                                    <div class="reviews-stars-wrap d-flex align-items-center">
                                        <div class="reviews-stars-outer">
                                            @for ($i = 0; $i < 5; $i++)
                                                <i
                                                    class="ti ti-star review-stars {{ $i < $product->average_rating ? 'text-warning' : '' }} "></i>
                                            @endfor
                                        </div>
                                        <div class="point-wrap">
                                            <span class="review-point">{{ $product->average_rating }}.0 /
                                                <span>{{ __('5.0') }}</span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="section-title">
                                    <h2>{{ $product->name }}</h2>
                                </div>
                                <div class="pdp-block pdp-info-block product-variant-description">
                                    {{-- <h5>{{__('Description:')}}</h5> --}}
                                    <p>{!! strip_tags($product->description) !!}</p>
                                </div>
                                <div class="count-price-wrp">
                                    <div class="count-left">
                                        <div class="price product-price-amount">
                                            <ins>
                                                <ins class="min_max_price" style="display: inline;">
                                                    {{ $currency_icon }}{{ $mi_price }} -
                                                    {{ $currency_icon }}{{ $ma_price }} </ins>
                                            </ins>
                                        </div>
                                        @if ($product->variant_product == 1)
                                            <h6 class="enable_option">
                                                @if ($product->product_stock > 0)
                                                    <span
                                                        class="stock">{{ $product->product_stock }}</span><small>{{ __(' in stock') }}</small>
                                                @endif
                                            </h6>
                                        @else
                                            <h6>
                                                @if ($product->track_stock == 0)
                                                    @if ($product->stock_status == 'out_of_stock')
                                                        <span>{{ __('Out of Stock') }}</span>
                                                    @elseif ($product->stock_status == 'on_backorder')
                                                        <span>{{ __('Available on backorder') }}</span>
                                                    @else
                                                        <span></span>
                                                    @endif
                                                @else
                                                    @if ($product->product_stock > 0)
                                                        <span>{{ $product->product_stock }} {{ __('  in stock') }}</span>
                                                    @endif
                                                @endif
                                            </h6>
                                        @endif
                                        <span class="product-price-error"></span>
                                        <div class="">
                                            {!! \App\Models\Testimonial::ProductReview($currentTheme, 1, $product->id) !!}
                                        </div>
                                        @if ($product->track_stock == 0 && $product->stock_status == 'out_of_stock')
                                        @else
                                            <form class="variant_form">
                                                <div class="quantity-select">
                                                    <span class="lbl">{{ __('quantity :') }}</span>
                                                    <div class="qty-spinner">
                                                        <button type="button" class="quantity-decrement change_price" data-product="{{ $product->id}}">
                                                            <svg width="12" height="2" viewBox="0 0 12 2"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M0 0.251343V1.74871H12V0.251343H0Z" fill="#61AFB3">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <input type="text" id="product-quantity" class="quantity product-quantity " data-cke-saved-name="quantity" name="qty" value="01" min="01" max="100" data-product="{{ $product->id}}">
                                                        <button type="button" class="quantity-increment change_price" data-product="{{ $product->id}}">
                                                            <svg width="12" height="12" viewBox="0 0 12 12"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M6.74868 5.25132V0H5.25132V5.25132H0V6.74868H5.25132V12H6.74868V6.74868H12V5.25132H6.74868Z"
                                                                    fill="#61AFB3"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>&nbsp;
                                                @include('front_end.common.product.variant')
                                            </form>
                                        @endif
                                    </div>
                                    <div class="count-right">
                                        @include('front_end.common.product.sale_counter')
                                        @include('front_end.common.product.custom_filed')
                                    </div>
                                </div>
                                <div class="stock_status"></div><br>
                                <div class="price product-price-amount price-value">
                                    {!! \App\Models\Product::getProductPrice($product, $store, $currentTheme) !!}
                                </div>
                                @if ($product->track_stock == 0 && $product->stock_status == 'out_of_stock')
                                @else
                                    <button
                                        class="addtocart-btn btn addcart-btn  addcart-btn-globaly price-wise-btn product_var_option"
                                        tabindex="0" product_id="{{ $product->id }}"
                                        variant_id="{{ $product->default_variant_id }}" qty="1">
                                        <span> {{ $section->common_page->section['cart_button']['text'] ?? __('Add to cart') }} </span>
                                        <svg viewBox="0 0 10 5">
                                            <path
                                                d="M2.37755e-08 2.57132C-3.38931e-06 2.7911 0.178166 2.96928 0.397953 2.96928L8.17233 2.9694L7.23718 3.87785C7.07954 4.031 7.07589 4.28295 7.22903 4.44059C7.38218 4.59824 7.63413 4.60189 7.79177 4.44874L9.43039 2.85691C9.50753 2.78197 9.55105 2.679 9.55105 2.57146C9.55105 2.46392 9.50753 2.36095 9.43039 2.28602L7.79177 0.69418C7.63413 0.541034 7.38218 0.544682 7.22903 0.702329C7.07589 0.859976 7.07954 1.11192 7.23718 1.26507L8.1723 2.17349L0.397965 2.17336C0.178179 2.17336 3.46059e-06 2.35153 2.37755e-08 2.57132Z">
                                            </path>
                                        </svg>
                                    </button>
                                    {!! \App\Models\Product::ProductcardButton($currentTheme, $slug, $product) !!}
                                @endif
                            </div>
                            <div class="product-hook">
                            @include('front_end.hooks.product_detail_info_button')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        {{-- tab section.  --}}
        @include('front_end.theme_common_table')
        @include('front_end.hooks.product_detail_slider')
        @include('front_end.sections.homepage.product_category_section')
        @include('front_end.sections.homepage.review_section')
        @include('front_end.sections.homepage.blog_section')
    @include('front_end.sections.partision.footer_section')
@endsection

@push('page-script')
@endpush
