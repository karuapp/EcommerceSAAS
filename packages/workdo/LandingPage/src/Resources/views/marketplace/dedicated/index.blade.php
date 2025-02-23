@extends('layouts.app')

@section('page-title')
    {{ __('Landing Page') }}
@endsection

@section('page-breadcrumb')
    {{__('Landing Page')}}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        @include('landing-page::marketplace.modules')
                <div class="row">
                    <div class="col-xl-3">
                        <div class="card sticky-top" style="top:30px">
                            <div class="list-group list-group-flush" id="useradd-sidenav">
                                @include('landing-page::marketplace.tab')
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9">
                    {{--  Start for all settings tab --}}
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>{{ __('Dedicated Theme Head Details') }}</h5>
                                    </div>
                                    <div id="p1" class="col-auto text-end text-primary h3">
                                        <a image-url="{{ get_file('packages/workdo/LandingPage/src/Resources/assets/infoimages/dedicated.png') }}"
                                           data-url="{{ route('info.image.view',['marketplace','dedicated']) }}" class="view-images">
                                            <i class="ti ti-info-circle pointer"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {{ Form::open(array('route' => array('dedicated_theme_header_store',$slug), 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('dedicated_theme_heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Form::text('dedicated_theme_heading',$settings['dedicated_theme_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'), 'id' => 'dedicated_theme_heading']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('dedicated_theme_description', __('Description'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('dedicated_theme_description', $settings['dedicated_theme_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id'=>'dedicated_theme_description','required'=>'required']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <input class="btn btn-print-invoice btn-primary btn-badge mr-2" type="submit" value="{{ __('Save Changes') }}">
                                </div>
                            {{ Form::close() }}
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h5>{{ __('Dedicated Theme Sections') }}</h5>
                                    </div>
                                    <div id="p1" class="col-auto text-end text-primary h3">
                                        <a image-url="{{ get_file('packages/workdo/LandingPage/src/Resources/assets/infoimages/dedicated.png') }}" data-id="1"
                                           data-url="{{ route('info.image.view',['marketplace','dedicated']) }}" class="view-images pt-2">
                                            <i class="ti ti-info-circle pointer"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto justify-content-end d-flex">
                                        <a href="javascript::void(0)" data-size="lg" data-url="{{ route('dedicated_theme_section_create',$slug) }}" data-ajax-popup="true"  data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Section')}}"  class="btn btn-sm btn-primary btn-badge">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{__('No')}}</th>
                                                <th>{{__('Name')}}</th>
                                                <th class="text-end">{{__('Action')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (is_array($dedicated_theme_sections) || is_object($dedicated_theme_sections))
                                                @php
                                                    $of_no = 1
                                                @endphp
                                                @foreach (($dedicated_theme_sections) as $key => $value)
                                                    <tr>
                                                        <td>{{ $of_no++ }}</td>
                                                        <td>{{ $value['dedicated_theme_section_heading'] }}</td>
                                                        <td>
                                                            <span class="d-flex gap-1 justify-content-end">
                                                                <a href="#" class="btn btn-sm btn-info btn-badge" data-url="{{ route('dedicated_theme_section_edit',[$slug,$key]) }}" data-ajax-popup="true" data-title="{{__('Edit Page')}}" data-size="lg" data-bs-toggle="tooltip"  title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                                    <i class="ti ti-pencil"></i>
                                                                </a>

                                                                {!! Form::open(['method' => 'GET', 'route' => ['dedicated_theme_section_delete',[$slug, $key]],'id'=>'delete-form-'.$key]) !!}
                                                                    <a href="#" class="btn btn-sm btn-danger btn-badge show_confirm" data-bs-toggle="tooltip"data-confirm="{{ __('Are You Sure?') }}"
                                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-text-yes="{{ __('Yes') }}" data-text-no="{{ __('No') }}"  title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="{{'delete-form-'.$key}}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    {{--  End for all settings tab --}}
                    </div>
                </div>
        </div>
    </div>
@endsection


@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush