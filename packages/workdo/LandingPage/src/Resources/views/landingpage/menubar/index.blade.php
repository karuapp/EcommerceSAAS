@extends('layouts.app')
@section('page-title')
    {{ __('Landing Page') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">{{ __('Landing Page') }}</li>
@endsection
@php
    $settings = \Workdo\LandingPage\Entities\LandingPageSetting::settings();
    $logo = get_file('storage/uploads/landing_page_image');
@endphp

@push('custom-script')
    <script>
        document.getElementById('site_logo').onchange = function() {
            var src = URL.createObjectURL(this.files[0])
            document.getElementById('image').src = src
        }
    </script>
    <script src="{{ asset('packages/workdo/LandingPage/src/Resources/assets/js/plugins/tinymce.min.js') }}" referrerpolicy="origin">
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item">{{ __('Landing Page') }}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">

                            @include('landing-page::layouts.tab')


                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    {{--  Start for all settings tab --}}

                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-10 col-md-10 col-sm-10">
                                    <h5>{{ __('Home Section') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Form::open(['route' => 'custom_store', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('site_logo', __('Site Logo'), ['class' => 'form-label']) }}
                                        <div class="logo-content mt-4">
                                            <img id="image" src="{{ check_file($settings['site_logo']) ? get_file($settings['site_logo']) : $logo . '/' . $settings['site_logo'] }}"
                                                class="big-logo" style="filter: drop-shadow(2px 3px 7px #011C4B);">
                                        </div>
                                        <div class="choose-files mt-5">
                                            <label for="site_logo">
                                                <div class="btn-badge bg-primary company_logo_update" style="cursor: pointer;">
                                                    <i class="ti ti-upload px-1"></i>{{ __('Choose File Here') }}
                                                </div>
                                                <input type="file" name="site_logo" id="site_logo"
                                                    class="form-control file" data-filename="site_logo">
                                            </label>
                                        </div>
                                        @error('site_logo')
                                            <div class="row">
                                                <span class="invalid-logo" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('site_description', __('Site Description'), ['class' => 'form-label']) }}
                                        {{ Form::text('site_description', $settings['site_description'], ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
                                        @error('mail_port')
                                            <span class="invalid-mail_port" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary btn-badge mr-2" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-lg-9 col-md-9 col-sm-9 col-9">
                                    <h5>{{ __('Menu Bar') }}</h5>
                                </div>
                            </div>
                        </div>
                        {{ Form::open(['route' => 'manage_ownwemenu', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('menus_id', __('Select Menus'), ['class' => 'form-label']) }}
                                        <select name="menus_id[]" data-role="tagsinput" id="menus_id" multiple>
                                            @foreach ($menus as $Key => $menu)
                                                <option @if (in_array($menu->id, $get_menu)) selected @endif
                                                    value={{ $menu->id }}>
                                                    {{ $menu->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label">{{ __('Enable Menus') }}</label>
                                    <div class="form-group d-flex align-items-center">
                                        <div class="form-check form-switch mx-2">
                                            <input type="checkbox" class="form-check-input" id="cust-theme-bg"
                                                name="enable_header"
                                                {{ isset($menusettings->enable_header) && $menusettings->enable_header == 'on' ? 'checked="checked"' : '' }} />
                                            <label class="form-check-label f-w-600"
                                                for="cust-theme-bg">{{ __('Header') }}</label>
                                        </div>
                                        <div class="form-check form-switch mx-2">
                                            <input type="checkbox" class="form-check-input" id="login"
                                                name="enable_login"
                                                {{ isset($menusettings->enable_login) && $menusettings->enable_login == 'on' ? 'checked="checked"' : '' }} />
                                            <label class="form-check-label f-w-600"
                                                for="login">{{ __('Login') }}</label>
                                        </div>
                                        <div class="form-check form-switch mx-2">
                                            <input type="checkbox" class="form-check-input" id="cust-darklayout"
                                                name="enable_footer"
                                                {{ isset($menusettings->enable_footer) && $menusettings->enable_footer == 'on' ? 'checked="checked"' : '' }} />
                                            <label class="form-check-label f-w-600"
                                                for="cust-darklayout">{{ __('Footer') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-primary mr-2 btn-badge" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                        {{ Form::close() }}
                    </div>
                    {{--  End for all settings tab --}}
                </div>
            </div>
        </div>
    </div>
@endsection
