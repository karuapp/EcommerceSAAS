@extends('layouts.theme_layout')
@section('customize-section')
<div class="container-fluid px-2">
    <header class="row preview-header-main">
        <div class="col-12">
            <div class="align-items-center pos-top-bar preview-header  bg-primary d-flex justify-content-between" style="padding: 20px 15px;">
                <div class="preview-header-left">
                    <a href="{{ route('theme-preview.index') }}" class="text-black"><i class="ti ti-home"
                        style="font-size: 20px;"></i> </a>
                    <span class="text-black">{{ __('Theme Preview') }}</span>
                </div>

                <div class="preview-header-right">
                    
                    <input type="hidden" id="publish_theme_url" name="theme_id" value="{{ route('publish-theme') }}">
                    <input type="hidden" id="publish_theme_id" name="theme_id" value="{{ $currentTheme }}">
                    <input type="hidden" id="publish_store_id" name="store_id" value="{{ $store_id }}">
                    <input type="hidden" id="publish_is_publish" name="is_publish" value="1">
                 
                    <button id="save-theme-btn" class="btn btn-secoundry btn-badge text-black">{{ __('Save as Draft') }} </button>
                   
                    <button id="publish-theme-btn" class="btn text-black" {{ $is_publish ? 'disabled' : '' }}>
                        {{ __('Publish') }}
                    </button>
                </div>

        </header>

    <div class="row mt-4">
        <div class="col-mb-6 col-lg-8">
            <div class="shop-theme-wrapper">
                <div class="sop-card card m-0">
                    <div class="card-header">
                        {{ __('Home Page')}}
                    </div>
                    <div class="card-body p-2">
                        <div class="right-content">
                            <div id="theme_preview_section">
                               <iframe src="{{ route('theme-preview-content',['theme_id' => request()->query('theme_id')]) }}" style="width: 100%;  border: none;" id="customize-theme"></iframe>
                            </div>
                            <div id="default_tool_bar" class="d-none">
                                <a class="option-button up-section-btn" href="#" id="up-section-btn" role="button">
                                    <i class="ti ti-arrow-up"></i>
                                </a>
                                <a class="option-button down-section-btn" href="#" id="down-section-btn" role="button">
                                    <i class="ti ti-arrow-down"></i>
                                </a>
                                <a class="option-button hide-section-bt" href="#" id="hide-section-btn" role="button">
                                    <i class="ti ti-eye-off"></i>
                                </a>
                                <a class="option-button show-section-btn" href="#" id="show-section-btn" role="button">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-mb-3 col-lg-4">
            <div class="sidebar_form">

            </div>
        </div>
    </div>
</div>
@endsection

