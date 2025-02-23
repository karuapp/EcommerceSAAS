@extends('layouts.app')

@section('page-title')
    {{ __('Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item mb-2" aria-current="page"><a href="{{ route('plan.index') }}">{{ __('Plan') }}</a></li>
    <li class="breadcrumb-item mb-2" aria-current="page">{{ __('Add') }}</li>
@endsection

@section('content')
    {{ Form::open(['route' => 'plan.store', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap g-2">
                <h3 class="h4 m-0">{{ __('Add Plan') }}</h3>
                <div class="d-flex align-items-center justify-content-end flex-wrap g-2">
                    @if (auth()->user()->type == 'super admin' && isset($setting['chatgpt_key']))
                        <a href="#" class="btn btn-primary btn-badge me-2 ai-btn" data-size="lg" data-ajax-popup-over="true"
                            data-url="{{ route('generate', ['plan']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                            <i class="fas fa-robot"></i> {{ __('Generate with AI') }}
                        </a>
                    @endif
                    <a href="{{ route('plan.index') }}"class="btn btn-badge btn-secondary ai-btn">{{ __('Back') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group col-md-12">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                            {{ Form::number('price', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Price'), 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
                            {!! Form::select('duration', $arrDuration, null, ['class' => 'form-control ', 'required' => 'required']) !!}
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('max_stores', __('Maximum Store'), ['class' => 'form-label']) }}
                                {{ Form::number('max_stores', null, ['class' => 'form-control', 'id' => 'max_stores', 'placeholder' => __('Enter Max Store'), 'required' => 'required']) }}
                                <span><small class="text-danger">{{ __("Note: '-1' for Unlimited") }}</small></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('max_products', __('Maximum Products Per Store'), ['class' => 'form-label']) }}
                                {{ Form::number('max_products', null, ['class' => 'form-control', 'id' => 'max_products', 'placeholder' => __('Enter Max Products'), 'required' => 'required']) }}
                                <span><small class="text-danger">{{ __("Note: '-1' for Unlimited") }}</small></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('max_users', __('Maximum Users Per Store'), ['class' => 'form-label']) }}
                                {{ Form::number('max_users', null, ['class' => 'form-control', 'id' => 'max_users', 'placeholder' => __('Enter Max User'), 'required' => 'required']) }}
                                <span><small class="text-danger">{{ __("Note: '-1' for Unlimited") }}</small></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('storage_limit', __('Storage Limit'), ['class' => 'form-label']) }}
                                <div class ='input-group'>
                                    {{ Form::number('storage_limit', null, ['class' => 'form-control', 'id' => 'storage_limit', 'placeholder' => __('Enter Storage Limit'), 'required' => 'required', 'min' => '0']) }}
                                    <span class="input-group-text bg-transparent">{{ __('MB') }}</span>
                                </div>
                                <span><small class="text-danger">{{ __('Note: upload size ( In MB)') }}</small></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                            <div class="custom-control form-switch pt-2 ps-0 gap-1 select-swich d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" name="enable_domain" id="enable_domain">
                                <label class="custom-control-label form-check-label"
                                    for="enable_domain">{{ __('Enable Domain') }}</label>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                            <div class="custom-control form-switch pt-2 ps-0 gap-1 select-swich d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" name="enable_subdomain"
                                    id="enable_subdomain">
                                <label class="custom-control-label form-check-label"
                                    for="enable_subdomain">{{ __('Enable Sub Domain') }}</label>
                            </div>
                        </div>

                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                            <div class="custom-control form-switch pt-2 ps-0 gap-1 select-swich d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" name="enable_chatgpt" id="enable_chatgpt">
                                <label class="custom-control-label form-check-label"
                                    for="enable_chatgpt">{{ __('Enable Chatgpt') }}</label>
                            </div>
                        </div>

                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                            <div class="custom-control form-switch pt-2 ps-0 gap-1 select-swich d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" name="pwa_store" id="pwa_store">
                                <label class="custom-control-label form-check-label"
                                    for="pwa_store">{{ __('Progressive Web App (PWA)') }}</label>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                            <div class="custom-control form-switch pt-2 ps-0 gap-1 select-swich d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" name="shipping_method" id="shipping_method">
                                <label class="custom-control-label form-check-label"
                                    for="shipping_method">{{ __('Shipping Method') }}</label>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                            <div class="custom-control form-switch pt-2 ps-0 gap-1 select-swich d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" name="enable_tax" id="enable_tax">
                                <label class="custom-control-label form-check-label"
                                    for="enable_tax">{{ __('Enable Taxes') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="row my-4">
                        <div class="col-xxl-2 col-xl-4 col-lg-6 col-md-6">
                            <label class="form-check-label" for="trial"></label>
                            <div class="form-group gap-1 d-flex align-items-center trial-switch">
                                <label for="trial" class="form-label">{{ __('Trial is enable(on/off)') }}</label>
                                <div class="form-check form-switch custom-switch-v1 float-end">
                                    <input type="checkbox" name="trial" class="form-check-input input-primary pointer"
                                        value="1" id="trial">
                                    <label class="form-check-label" for="trial"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-10 col-xl-8 col-lg-6 col-md-6">
                            <div class="form-group plan_div d-none">
                                {{ Form::label('trial_days', __('Trial Days'), ['class' => 'form-label']) }}
                                {{ Form::number('trial_days', null, ['class' => 'form-control trial_days', 'placeholder' => __('Enter Trial days'), 'step' => '1', 'min' => '1']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        @if (isset($modules) && count($modules) > 0)
                            <div class="all-plans">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="h4 m-0">{{ __('Select Module') }}</h3>
                                    <div class="col-md-2">
                                        <input type="text" id="addon-search" placeholder="{{ __('Search Modules') }}"
                                            class="form-control btn-badge">
                                    </div>
                                </div>
                                <hr>
                                <div class="plan-module">
                                    <div class="row" id="addon-list">
                                        @if (isset($modules) && count($modules))
                                            @foreach ($modules as $module)
                                                @if (in_array($module->name, getshowModuleList()))
                                                    <div class="col-xl-4 col-lg-6 col-md-6 addon-item">
                                                        <div class="card">
                                                            <div class="card-body p-3 border border-primary">
                                                                <div
                                                                    class="gap-2 d-flex align-items-center justify-content-between">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="p-3 border border-primary img-checkbox"
                                                                            data-checkbox="modules_{{ $module->name }}">
                                                                            <div class="theme-avtar">
                                                                                <img src="{{ get_module_img($module->name) }}{{ '?' . time() }}"
                                                                                    alt="{{ $module->name }}"
                                                                                    class="img-user"
                                                                                    style="max-width: 100%">
                                                                            </div>
                                                                        </div>
                                                                        <div class="ms-3">
                                                                            <label for="modules_{{ $module->name }}">
                                                                                <h5 class="mb-0 pointer">
                                                                                    {{ ucwords($module->alias) }}</h5>
                                                                            </label>
                                                                            <p class="text-muted text-sm mb-0">
                                                                                {{ isset($module->description) ? $module->description : '' }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input modules"
                                                                            name="modules[]" value="{{ $module->name }}"
                                                                            id="modules_{{ $module->name }}"
                                                                            type="checkbox">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="col-lg-12 col-md-12">
                                                <div class="card p-5">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="ms-3 text-center">
                                                            <h3>{{ __('Add-on Not Available') }}</h3>
                                                            <p class="text-muted">{{ __('Click ') }}<a
                                                                    href="{{ route('module.index') }}">{{ __('here') }}</a>
                                                                {{ __('To Activate Add-on') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="select-themes mt-4">
                            <div class="horizontal mt-3">
                                <div class="verticals twelve">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h3 class="h4 m-0">{{ __('Select Themes') }}</h3>
                                        <div class="col-md-2">
                                            <input type="text" id="theme-search"
                                                placeholder="{{ __('Search Themes') }}" class="form-control btn-badge mt-3">
                                        </div>
                                    </div>
                                    <hr>
                                    <ul class="uploaded-pics p-3" id="theme-list">
                                        <div class="row">
                                            @foreach ($theme as $key => $v)
                                                <li class="col-xxl-3 col-xl-4 col-md-6 theme-item">
                                                    <input type="checkbox" id="checkthis{{ $key }}"
                                                        value="{{ $v->theme_id }}" name="themes[]" checked />
                                                    <label for="checkthis{{ $key }}">
                                                        <span class="theme-label">{{ ucfirst($v->theme_id) }}</span>
                                                        <img
                                                            src="{{ asset('themes/' . $v->theme_id . '/theme_img/img_1.png') }}" />
                                                    </label>
                                                </li>
                                            @endforeach
                                        </div>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'id' => 'description', 'rows' => 2, 'placeholder' => __('Enter Description')]) }}
                        </div>
                    </div>

                    <div class="modal-footer pb-0">
                        <a href="{{ route('plan.index') }}"class="btn btn-badge btn-secondary">{{ __('Back') }} </a>
                        <input type="submit" value="{{ __('Create') }}" class="btn btn-badge btn-primary ms-2">
                    </div>
                </div>
            </div>
        </div>
    </div>


    {!! Form::close() !!}


@endsection
@push('custom-script')
    <script>
        $(document).on('change', '#trial', function() {
            if ($(this).is(':checked')) {
                $('.plan_div').removeClass('d-none');
                $('#trial_days').attr("required", true);

            } else {
                $('.plan_div').addClass('d-none');
                $('#trial_days').removeAttr("required");
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.img-checkbox').forEach(function(element) {
                element.addEventListener('click', function() {
                    const checkboxId = this.getAttribute('data-checkbox');
                    const checkbox = document.getElementById(checkboxId);
                    checkbox.checked = !checkbox.checked;
                });
            });
        });

        $(document).on('keyup', '#theme-search', function() {
            var value = $(this).val().toLowerCase();
            $('#theme-list .theme-item').filter(function() {
                $(this).toggle($(this).find('.theme-label').text().toLowerCase().indexOf(value) > -1)
            });
        });

        $(document).on('keyup', '#addon-search', function() {
            var value = $(this).val().toLowerCase();
            $('#addon-list .addon-item').filter(function() {
                $(this).toggle($(this).find('h5').text().toLowerCase().indexOf(value) > -1)
            });
        });
    </script>
@endpush
