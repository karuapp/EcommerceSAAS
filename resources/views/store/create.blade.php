@extends('layouts.app')

@section('page-title')
    {{ __('Store Create') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Add') }}</li>
@endsection

@section('content')
    {!! Form::open(['route' => 'stores.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'autocomplete' => 'off']) !!}

    @if (auth()->user()->type == 'super admin' && !empty($setting['chatgpt_key']))
        <div class="d-flex justify-content-end">
            <a href="#" class="btn btn-primary btn-badge me-2 ai-btn" data-size="lg" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['store']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i> {{ __('Generate with AI') }}
            </a>
        </div>
    @endif

    @if (auth()->user()->type == 'admin' && $plan && $plan->enable_chatgpt == 'on')
        <div class="d-flex justify-content-end">
            <a href="#" class="btn btn-primary me-2 ai-btn btn-badge" data-size="lg" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['store']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i> {{ __('Generate with AI') }}
            </a>
        </div>
    @endif

    <div class="row">
        <div class="form-group col-md-12">
            {!! Form::label('storename', __('Store Name'), ['class' => 'form-label']) !!}
            {!! Form::text('storename', null, [
                'class' => 'form-control',
                'id' => 'storename',
                'placeholder' => __('Enter Store Name'),
                'required' => 'true',
            ]) !!}
        </div>

        @if (auth()->user()->type == 'admin')
            <div class="form-group col-md-12">
                @php
                    $store = App\Models\Store::where('id', getCurrentStore())->first();
                @endphp
                <div class="mt-4 select-themes plan-select">
                    <div class="d-flex align-items-center justify-content-between">
                        {!! Form::label('', __('Theme'), ['class' => 'form-label']) !!}
                        <div class="col-md-2">
                            <input type="text" id="theme-search" placeholder="{{ __('Search Themes') }}"
                                class="mt-3 form-control">
                        </div>
                    </div>
                    <ul class="pt-3 uploaded-pics row" id="theme-list">
                        @foreach ($themes as $key => $value)
                            <li class="col-xxl-3 col-xl-4 col-md-6 theme-item  p-1">
                                <input class="form-check-input email-template-checkbox d-none" type="radio"
                                    id="theme_{{ !empty($value) ? $value : '' }}" name="theme_id"
                                    value="{{ !empty($value) ? $value : '' }}"
                                    @if (!empty($value) ? $store->theme_id == $value : 0) checked="checked" @endif />
                                <label for="theme_{{ !empty($value) ? $value : '' }}">
                                    <span class="theme-label">{{ ucfirst($value) }}</span>
                                    <img src="{{ asset('themes/' . $value . '/theme_img/img_1.png') }}" />
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (auth()->user()->type == 'super admin')
            <div class="form-group col-md-12">
                {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'placeholder' => __('Enter Name')]) !!}
            </div>
            <div class="form-group col-md-12">
                {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}
                {!! Form::email('email', null, ['class' => 'form-control', 'id' => 'email', 'placeholder' => __('Enter Email')]) !!}
            </div>
            <div class="form-group col-md-12">
                {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}
                {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder' => __('Enter Password')]) }}
            </div>
        @endif

        <div class="pb-0 modal-footer">
            <a href="{{ route('dashboard') }}"class="btn btn-secondary btn-badge me-2">{{ __('Back') }} </a>
            <input type="submit" value="Create" class="btn btn-primary btn-badge">
        </div>
    </div>
    {!! Form::close() !!}


@endsection

@push('custom-script')
    <script>
        $(document).on('keyup', '#theme-search', function() {
            var value = $(this).val().toLowerCase();
            $('#theme-list .theme-item').filter(function() {
                $(this).toggle($(this).find('.theme-label').text().toLowerCase().indexOf(value) > -1)
            });
        });
    </script>
@endpush
