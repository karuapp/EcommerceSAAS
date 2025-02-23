<div class="product-btn-wrp">
    @php
    $store = \App\Models\Store::where('slug', $slug)->first();
    @endphp
    @if (module_is_active('ProductQuickView'))
        @php
            $enable_quickview = \App\Models\Utility::GetValueByName('enable_product_quickview', $store->theme_id, $store->id);
        @endphp
        @if (isset($enable_quickview) && $enable_quickview == 'on')
            @include('product-quick-view::pages.button', ['product_slug' => $product->slug ?? null, 'slug' => $slug ?? null, 'currentTheme' => $currentTheme])
        @endif
    @endif

    @if (module_is_active('ProductCompare'))
        @php
            $enable_compare = \App\Models\Utility::GetValueByName('enable_product_compare', $store->theme_id, $store->id);
        @endphp
        @if (isset($enable_compare) && $enable_compare == 'on')
            @include('product-compare::pages.button', ['product_slug' => $product->slug ?? null, 'slug' => $slug ?? null, 'currentTheme' => $currentTheme])
        @endif
    @endif


    @if (module_is_active('ProductsPDF'))
        @php
            $ProductsPDF_enable = \App\Models\Utility::GetValueByName('products_pdf_enable', $store->theme_id, $store->id);
        @endphp
        @if (isset($ProductsPDF_enable) && $ProductsPDF_enable == 'on')

            @include('products-pdf::pages.button', ['product_slug' => $product->slug ?? null, 'slug' => $slug ?? null, 'currentTheme' => $currentTheme])
        @endif
    @endif
    @if (module_is_active('ProductCSV'))
        @php
            $product_csvswich_is_enable = \App\Models\Utility::GetValueByName('product_csvswich_is_enable', $store->theme_id, $store->id);
        @endphp
        @if (isset($product_csvswich_is_enable) && $product_csvswich_is_enable == 'on')
            @include('product-csv::pages.button', ['product_slug' => $product->slug ?? null, 'slug' => $slug ?? null, 'currentTheme' => $currentTheme])
        @endif
    @endif
</div>
