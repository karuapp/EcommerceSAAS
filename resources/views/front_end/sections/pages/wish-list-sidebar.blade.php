<style>
    .wishDrawer .closewish {
    position: absolute;
    left: -38px;
    top: 20px;
    width: 20px;
    height: 20px;
    opacity: 0;
    visibility: hidden;
}
.wishOpen .wishDrawer .closewish {
    opacity: 1;
    visibility: visible;
}
</style>
<div class="wishDrawer">
    <div class="mini-wish-header">
       <h4>{{ __('My wish') }}</h4>
       {{-- <div class="wish-tottl-itm">0{{ __('Items') }}</div> --}}
       <a href="#" class="closewish">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path
                    d="M20 1.17838L18.8216 0L10 8.82162L1.17838 0L0 1.17838L8.82162 10L0 18.8216L1.17838 20L10 11.1784L18.8216 20L20 18.8216L11.1784 10L20 1.17838Z"
                    fill="white"></path>
            </svg>
        </a>
    </div>
    <div id="wish-body" class="mini-wish-has-item">
       <div class="mini-wish-body">
        @if (!empty($response->data->data))
        @foreach ($response->data->data as $item)
          <div class="mini-wish-item">
             <div class="mini-wish-image">
                <a href="{{url($slug.'/product/'.$item->product_data->slug)}}" title="SPACE BAG">
                   <img src="{{ get_file($item->product_image ?? '', $currentTheme) }}" alt="plant1">
                </a>
             </div>
             <div class="mini-wish-details" style="color: black;">
                <p class="mini-wish-title"><a href="{{url($slug.'/product/'.$item->product_data->slug)}}"> {{$item->product_name}}</a></p>
                @if ($item->variant_id != 0)
                    {!! \App\Models\ProductVariant::variantlist($item->variant_id) !!}
                @endif
                @if ($item->product_data->variant_product == 1)
                    @php
                        $variant = json_decode($item->product_data->product_attribute);
                        $variantNameArray = !empty($item->product_data->DefaultVariantData->variant)
                            ? explode('-', $item->product_data->DefaultVariantData->variant)
                            : [];
                    @endphp

                    @foreach ($variant as $key => $value)
                        @php
                            $p_variant = App\Models\Utility::ProductAttribute($value->attribute_id);
                            $attribute = json_decode($p_variant);
                            $propertyKey = 'for_variation_' . $attribute->id;
                            $variationOption = $value->$propertyKey;
                        @endphp

                        @if ($variationOption == 1)
                            <form class="wishlist_variant_form">
                                <div class="product-selectors">
                                    <div class="size-select">
                                        <span class="lbl">{{ $attribute->name }} :</span>
                                        <select data-product="{{ $item->product_id }}" class="custom-select-btn product_variatin variant_loop_value" name="varint[{{ $attribute->name }}]">
                                            <option value="">{{ __('Select an option') }}</option>
                                            @foreach ($value->values as $variant1)
                                                @foreach (explode('|', $variant1) as $p)
                                                    @php
                                                        $id = App\Models\ProductAttributeOption::find($p);
                                                        $optionValue = optional($id)->terms; // Use optional to handle null
                                                    @endphp
                                                    @if ($optionValue)
                                                        <option>{{ $optionValue }}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @endforeach

                    <div class="pvarprice d-flex align-items-center justify-content-between"></div>
                    <div class="price wish_product-price-amount">
                        <span class="wish_product_final_price"></span>
                    </div>
                @else
                    <div class="pvarprice d-flex align-items-center justify-content-between">
                        <div class="price">
                            <ins>{{ currency_format_with_sym( $item->final_price, $store->id, $currentTheme) ?? SetNumberFormat($item->final_price) }}</ins><del>{{ currency_format_with_sym( $item->original_price, $store->id, $currentTheme) ?? SetNumberFormat($item->original_price) }}</del>
                        </div>
                    </div>
                @endif
             </div>

             <a href="JavaScript:void(0)" class="btn addtocart-btn btn addcart-btn  addcart-btn-globaly price-wise-bbtn product_vari_option" style ="width: 63%;" product_id="{{ $item->product_id }}" variant_id="{{ $item->variant_id}}" qty="1">
                <span>{{ $section->common_page->section['cart_button']['text'] ?? __('Add to cart') }}</span>
                <svg viewBox="0 0 10 5">
                    <path
                        d="M2.37755e-08 2.57132C-3.38931e-06 2.7911 0.178166 2.96928 0.397953 2.96928L8.17233 2.9694L7.23718 3.87785C7.07954 4.031 7.07589 4.28295 7.22903 4.44059C7.38218 4.59824 7.63413 4.60189 7.79177 4.44874L9.43039 2.85691C9.50753 2.78197 9.55105 2.679 9.55105 2.57146C9.55105 2.46392 9.50753 2.36095 9.43039 2.28602L7.79177 0.69418C7.63413 0.541034 7.38218 0.544682 7.22903 0.702329C7.07589 0.859976 7.07954 1.11192 7.23718 1.26507L8.1723 2.17349L0.397965 2.17336C0.178179 2.17336 3.46059e-06 2.35153 2.37755e-08 2.57132Z">
                    </path>
                </svg>
            </a>

            <a href="javascript:;" class="remove-btn py-1 delete_wishlist" data-id="{{ $item->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true" focusable="false" role="presentation" class="icon icon-remove ">
                    <path d="M14 3h-3.53a3.07 3.07 0 00-.6-1.65C9.44.82 8.8.5 8 .5s-1.44.32-1.87.85A3.06 3.06 0 005.53 3H2a.5.5 0 000 1h1.25v10c0 .28.22.5.5.5h8.5a.5.5 0 00.5-.5V4H14a.5.5 0 000-1zM6.91 1.98c.23-.29.58-.48 1.09-.48s.85.19 1.09.48c.2.24.3.6.36 1.02h-2.9c.05-.42.17-.78.36-1.02zm4.84 11.52h-7.5V4h7.5v9.5z" fill="currentColor"></path>
                    <path d="M6.55 5.25a.5.5 0 00-.5.5v6a.5.5 0 001 0v-6a.5.5 0 00-.5-.5zM9.45 5.25a.5.5 0 00-.5.5v6a.5.5 0 001 0v-6a.5.5 0 00-.5-.5z" fill="currentColor"></path>
                </svg>
            </a>
          </div>
          @endforeach
        @else
            <p class="emptywish text-center">{{ __('You have no items in your shopping wish.') }}</p>
        @endif
       </div>

    </div>
</div>

<script>
    $(document).on('change', '.product_variatin', function(e) {
    var productId = $(this).data('product');
    product_variant_price(productId);
});

function product_variant_price(productId) {
    var data = $('.wishlist_variant_form').serialize();
    var data = data + '&product_id='+productId;
    var size_data = globalSizeChartId;
    var data =  data + '&size_data='+size_data;

    $.ajax({
        url: productPrice,
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        context: this,

        success: function(response) {            
            $('.addcart-btn.addcart-btn-globaly').attr('variant_id', '0');
            if (response.status == 'error') {
                show_toastr('Error', response.message, 'error');
                $('.quantity').val(response.qty);
                $('.product_vari_option').attr('variant_id', response.variant_id);
            } else {
                $('.wish_product-price-amount .wish_product_final_price').html(response.currency + response.original_price);
                $('.currency').html(response.currency);
                $('.currency-type').html(response.currency_name);
                $('.wish_product-price-amount .product_orignal_price').html(response.currency + response.product_original_price);
                $('.product_tax_price').html(response.total_tax_price + ' ' + response.currency_name);
                $('.addcart-btn.addcart-btn-globaly').attr('variant_id', response.variant_id);
                $('.addcart-btn.addcart-btn-globaly').attr('qty', response.qty);
                $('.quick-checkout-button').attr('variant_id', response.variant_id);
                $('.quick-checkout-button').attr('qty', response.qty);
                $(".enable_option").hide();
                $('.product-variant-description').html(response.description);
                if (size_module_active === true) {
                    if(response.total_tax_price != '' && response.variant_id == 0)
                    {
                        $('.stock_status').show();
                        var message = '<span class=" mb-0"> Tax Price : ' + response.currency + response.total_tax_price + '</span>';
                        $('.stock_status').html(message);
                    }
                }
                if (response.enable_option_data == true) {
                    if (response.stock <= 0) {
                        $('.stock').parent().hide(); // Hide the parent container of the .stock element
                    } else {
                        $('.stock').html(response.stock);
                        $('.enable_option').show();
                    }
                }
                if (size_module_active === false) {
                    if (response.stock_status != '') {
                        if (response.stock_status == 'out_of_stock') {
                            $('.price-value').hide();
                            $('.wishlist_variant_form').hide();
                            $('.price-wise-bbtn').hide();
                            $('.stock_status').show();
                            var message = '<span class=" mb-0"> Out of Stock.</span>';
                            $('.stock_status').html(message);

                        } else if (response.stock_status == 'on_backorder') {
                            $('.stock_status').show();
                            var message = '<span class=" mb-0">Available on backorder.</span>';
                            $('.stock_status').html(message);

                        } else {
                            $('.stock_status').hide();
                        }
                    }
                }
                if (response.variant_product == 1 && response.variant_id == 0) {
                    $('.wish_product-price-amount .product_orignal_price').hide();
                    $('.wish_product-price-amount .wish_product_final_price').hide();
                    $('.min_max_price').show();
                    $('.wish_product-price-amount').hide();
                    $('.product-price-error').show();
                    var message =
                        '<span class=" mb-0 text-danger"> This product is not available.</span>';
                    $('.product-price-error').html(message);
                } else {
                    $('.product-price-error').hide();
                    $('.wish_product-price-amount .product_orignal_price').show();
                    $('.currency').show();
                    $('.wish_product-price-amount .wish_product_final_price').show();
                    $('.wish_product-price-amount').show();
                }
                if (response.product_original_price == 0 && response.original_price == 0 && response.qty > 0) {
                    $('.wish_product-price-amount').hide();
                    $('.wishlist_variant_form').hide();
                    $('.price-wise-bbtn').hide();
                }
            }
        }
    });
}

</script>
