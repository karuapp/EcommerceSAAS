<span class="d-flex gap-1 justify-content-end">
    @if ( in_array($data->id,$upddata))
        @permission('Edit Woocommerce SubCategory')
            <a href="{{ route('woocom_sub_category.edit', $data->id) }}"  class="btn btn-sm btn-info"
                data-title="{{ __('Sync Again') }}" data-bs-toggle="tooltip"
                title="{{ __('Sync Again') }}">
                <i class="ti ti-refresh "></i>
            </a>
        @endpermission
    @else
        @permission('Create Woocommerce SubCategory')
        <a href="{{ route('woocom_sub_category.show', $data->id) }}" class="btn btn-sm btn-primary"
            data-title="{{__('Add Sub Category')}}"
            data-bs-toggle="tooltip" title="{{ __('Create Sub Category') }}">
            <i class="ti ti-plus"></i>
        </a>
        @endpermission
    @endif
</span>
