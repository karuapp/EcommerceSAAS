@if ($latestSales)
    @foreach ($latestSales as $productId => $saleData)
        <input type="hidden" class="flash_sale_start_date"
            value={{ $saleData['start_date'] }}>
        <input type="hidden" class="flash_sale_end_date"
            value={{ $saleData['end_date'] }}>
        <input type="hidden" class="flash_sale_start_time"
            value={{ $saleData['start_time'] }}>
        <input type="hidden" class="flash_sale_end_time"
            value={{ $saleData['end_time'] }}>
        <div id="flipdown" class="flipdown"></div>
    @endforeach
@endif
@if(module_is_active('PreOrder'))
    @include('pre-order::pages.preSaleCoundown')
@endif
@if(module_is_active('ProductImageZoom'))
    @if (isset($setting['product_image_zoom_is_enable']) && $setting['product_image_zoom_is_enable'] == 'on')
        @include('product-image-zoom::ProductImageZoom-details')
    @endif
@endif
