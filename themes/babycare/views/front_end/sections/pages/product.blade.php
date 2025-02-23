@extends('front_end.layouts.app')
@section('page-title')
    {{ __('Products') }}
@endsection
@php

@endphp

@section('content')
    @include('front_end.sections.partision.header_section')

    <section class="product-main-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-12 ">
                    <div class="pdp-sliders-wrapper">
                        <div class="pdp-main-slider">
                            @if (!empty($product->Sub_image($product->id)['data']))
                                @foreach ($product->Sub_image($product->id)['data'] as $item)
                                    <div class="pdp-main-itm">
                                        <div class="pdp-itm-inner">
                                            <img src="{{ get_file($item->image_path,$currentTheme) }}" alt="product">

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
                                <div class="pdp-main-itm">
                                    <div class="pdp-itm-inner">
                                        <img src="{{ get_file($product->cover_image_path,$currentTheme) }}" alt="product">
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
                        <div class="pdp-thumb-wrap">
                            <div class="pdp-thumb-slider">
                                @foreach ($product->Sub_image($product->id)['data'] as $item)
                                    <div class="pdp-thumb-itm">
                                        <div class="pdp-thumb-inner">
                                            <img src="{{ get_file($item->image_path,$currentTheme) }}"
                                                alt="product">
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

                <div class="col-md-6 col-12">
                    <div class="pdp-left-column">
                        <a href="javascript:void(0)" class="wishbtn wishbtn-globaly"
                            product_id="{{ $product->id }}"
                            in_wishlist="{{ $product->in_whishlist ? 'remove' : 'add' }}">
                            {{ __('Add to wishlist') }}
                            <span class="wish-ic">
                                <i class="{{ $product->in_whishlist ? 'fa fa-heart' : 'ti ti-heart' }}"></i>
                            </span>
                        </a>
                        <div class="label">{{ $product->label->name ?? ''  }}</div>
                        <h2>{{ $product->name }}</h2>
                        <div class="pdp-block pdp-info-block product-variant-description">
                            <p>{{ strip_tags($product->description) }}</p>
                        </div><br>
                        <div class="price product-price-amount">
                            <ins>
                                <ins class="min_max_price" style="display: inline;">
                                    {{ $currency }}{{ $mi_price }} -
                                    {{ $currency }}{{ $ma_price }} </ins>
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
                            <div class="pdp-price-section d-flex align-items-center justify-content-between">
                                <form class="variant_form w-100 p-form">
                                    <div class="prorow-lbl-qntty">
                                        <div class="product-labl d-block">{{ __('Quantity') }}</div>
                                        <div class="qty-spinner">
                                            <button type="button" class="quantity-decrement change_price" data-product="{{$product->id}}">
                                                <svg width="12" height="2" viewBox="0 0 12 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 0.251343V1.74871H12V0.251343H0Z" fill="#61AFB3">
                                                    </path>
                                                </svg>
                                            </button>
                                            <input type="text" id="product-quantity" class="quantity product-quantity " data-cke-saved-name="quantity" name="qty" value="01" min="01" max="100" data-product="{{ $product->id}}">
                                            <button type="button" class="quantity-increment change_price" data-product="{{$product->id}}">
                                                <svg width="12" height="12" viewBox="0 0 12 12"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M6.74868 5.25132V0H5.25132V5.25132H0V6.74868H5.25132V12H6.74868V6.74868H12V5.25132H6.74868Z"
                                                        fill="#61AFB3"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="prorow-lbl-color">
                                    @include('front_end.common.product.variant')
                                    </div>
                                </form>
                            </div>
                        @endif
                        <div class="stock_status"></div><br>
                        @include('front_end.common.product.custom_filed')

                        <div class="count-right">
                            @include('front_end.common.product.sale_counter')
                            <div class="price-btn-wrp">
                                <div class="price product-price-amount price-value">
                                {!! \App\Models\Product::getProductPrice($product, $store, $currentTheme) !!}

                                </div>

                                <div class="pdp-add-to-cart-btn d-flex align-items-center justify-content-between">
                                    <div class="product-hook">
                                    @if ($product->track_stock == 0 && $product->stock_status == 'out_of_stock')
                                    @else
                                        <button
                                            class="btn theme-btn addcart-btn addbtn addcart-btn-globaly price-wise-btn product_var_option"
                                            product_id="{{ $product->id }}"
                                            variant_id="0" qty="1">
                                            {{ $section->common_page->section['cart_button']['text'] ?? __('Add to cart') }}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15"
                                                viewBox="0 0 16 15" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M4.22012 7.5C4.02701 7.5 3.88012 7.6734 3.91187 7.86387L4.51047 11.4555C4.61093 12.0582 5.13242 12.5 5.74347 12.5H10.2578C10.8689 12.5 11.3904 12.0582 11.4908 11.4555L12.0894 7.86387C12.1212 7.6734 11.9743 7.5 11.7812 7.5H4.22012ZM3.11344 6.25C2.72722 6.25 2.43345 6.59679 2.49694 6.97775L3.27748 11.661C3.47839 12.8665 4.52137 13.75 5.74347 13.75H10.2578C11.4799 13.75 12.5229 12.8665 12.7238 11.661L13.5044 6.97775C13.5678 6.59679 13.2741 6.25 12.8879 6.25H3.11344Z"
                                                    fill="#12131A"></path>
                                                <path
                                                    d="M6.75 8.75C6.40482 8.75 6.125 9.02982 6.125 9.375V10.625C6.125 10.9702 6.40482 11.25 6.75 11.25C7.09518 11.25 7.375 10.9702 7.375 10.625V9.375C7.375 9.02982 7.09518 8.75 6.75 8.75Z"
                                                    fill="#12131A"></path>
                                                <path
                                                    d="M9.25 8.75C8.90482 8.75 8.625 9.02982 8.625 9.375V10.625C8.625 10.9702 8.90482 11.25 9.25 11.25C9.59518 11.25 9.875 10.9702 9.875 10.625V9.375C9.875 9.02982 9.59518 8.75 9.25 8.75Z"
                                                    fill="#12131A"></path>
                                                <path
                                                    d="M7.19194 2.31694C7.43602 2.07286 7.43602 1.67714 7.19194 1.43306C6.94786 1.18898 6.55214 1.18898 6.30806 1.43306L3.80806 3.93306C3.56398 4.17714 3.56398 4.57286 3.80806 4.81694C4.05214 5.06102 4.44786 5.06102 4.69194 4.81694L7.19194 2.31694Z"
                                                    fill="#12131A"></path>
                                                <path
                                                    d="M8.80806 2.31694C8.56398 2.07286 8.56398 1.67714 8.80806 1.43306C9.05214 1.18898 9.44786 1.18898 9.69194 1.43306L12.1919 3.93306C12.436 4.17714 12.436 4.57286 12.1919 4.81694C11.9479 5.06102 11.5521 5.06102 11.3081 4.81694L8.80806 2.31694Z"
                                                    fill="#12131A"></path>
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M12.375 5H3.625C3.27982 5 3 5.27982 3 5.625C3 5.97018 3.27982 6.25 3.625 6.25H12.375C12.7202 6.25 13 5.97018 13 5.625C13 5.27982 12.7202 5 12.375 5ZM3.625 3.75C2.58947 3.75 1.75 4.58947 1.75 5.625C1.75 6.66053 2.58947 7.5 3.625 7.5H12.375C13.4105 7.5 14.25 6.66053 14.25 5.625C14.25 4.58947 13.4105 3.75 12.375 3.75H3.625Z"
                                                    fill="#12131A"></path>
                                            </svg>
                                        </button>
                                    {!! \App\Models\Product::ProductcardButton($currentTheme, $slug, $product) !!}
                                    @endif
                                    @include('front_end.hooks.product_detail_info_button')
                                    </div>
                                </div>
                            </div>
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


    @include('front_end.sections.homepage.subscribe_section')

    @include('front_end.sections.partision.footer_section')
@endsection

@push('page-script')
    
@endpush
