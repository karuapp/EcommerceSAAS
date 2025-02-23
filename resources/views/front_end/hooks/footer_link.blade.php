<div class="external-btns">
    @stack('CommmetIconView')
    @if(isset($whatsapp_setting_enabled) && !empty($whatsapp_setting_enabled))
        <div class="floating-wpp"></div>
    @endif
</div>
<div class="external-left-btn">
    <div class="show-btn btn"><i class="fas fa-sun"></i></div>
    <div class="left-btn-inner">
        @stack('addCompareButton')
        @stack('addCatelogRequestButton')
        @stack('DonationFormButton')
        @stack('freeShippingPopupButton')
        @stack('boostSalesModelPopup')
        @stack('couponRequestbutton')
    </div>
</div>

@push('page-script')
<script>
    $(document).ready(function() {
        // Check if .left-btn-inner contains any children
        if ($('.left-btn-inner').children().length > 0) {
            // If there are children, show the parent container
            $('.external-left-btn').show();
        } else {
            // If there are no children, hide the parent container
            $('.external-left-btn').hide();
        }
    });
    </script>
@endpush