@extends('layouts.app')
@section('page-title', __('Menus'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ownermenus.index') }}">{{ __('Menus') }}</a></li>
    <li class="breadcrumb-item">{{ $desiredMenu->name }}</li>
@endsection
@section('action-button')
    @permission('Create LandingPage Menu')
    <div class="text-end d-flex all-button-box justify-content-md-end justify-content-center">
        <a href="#" class="btn btn-sm btn-primary btn-badge p-2" data-ajax-popup="true" data-size="md" data-title="{{__('Add Menus')}}"
            data-url="{{ route('ownermenus.create') }}" data-bs-toggle="tooltip" title="{{ __('Add Menu') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
    @endpermission
@endsection
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="container-fluid">
                        <div class="row" id="main-row">
                            <div class="col-xxl-3 col-lg-5 col-md-6 cat-form menu-tab-view" id="accordionExample ">
                                <h5>{{ __('Add Menu Items') }}</h5>
                                <div class="accordion accordion-flush" id="">
                                    <div id="" class="accordion-item card ">
                                        <h2 class="accordion-header" id="COD">
                                            <button class="accordion-button according-delete-input" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#pages-list"
                                                aria-controls="pages-list" aria-expanded="false">
                                                <span class="d-flex align-items-center">
                                                    {{ __('Pages') }}
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="pages-list" class="accordion-collapse collapse"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                @foreach ($pages as $page_id => $page)
                                                    <div class="custom-control custom-checkbox">
                                                        {!! Form::checkbox('select-page[]', $page['menubar_page_name'], null, [
                                                            'id' => 'pageCheck' . $page_id,
                                                            'class' => 'form-check-input',
                                                        ]) !!}
                                                        {!! Form::label('pageCheck' . $page_id, $page['menubar_page_name'], ['class' => 'custom-control-label mx-1']) !!}
                                                    </div>
                                                @endforeach
                                                <div class="mt-2 d-flex justify-content-between">
                                                    <div class="custom-control custom-checkbox d-flex">
                                                        {!! Form::checkbox(null, null, null, ['id' => 'select-all-pages', 'class' => 'form-check-input']) !!}
                                                        {!! Form::label('select-all-pages', __('Select All'), [
                                                            'class' => 'custom-control-label mx-1 btn-badge btn btn-sm btn-outline-info d-flex align-items-center flex-1', 
                                                        ]) !!}
                                                    </div>
                                                    @permission('Create LandingPage Menu')
                                                    <button type="button" id="add-pages"
                                                        class="pull-right btn-badge btn btn-primary btn-sm">{{ __('Add to Menu') }}</button>
                                                    @endpermission
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion accordion-flush" id="">
                                    <div id="" class="accordion-item card ">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button according-delete-input" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#custom-links"
                                                aria-controls="custom-links" aria-expanded="false">
                                                <span class="d-flex align-items-center">
                                                    {{ __('Custom Links ') }}
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="custom-links" class="accordion-collapse collapse show"
                                            data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <div class="form-group">
                                                    {!! Form::label('url', __('URL'), ['class' => 'form-label']) !!} <span class="validation-required">*</span>
                                                    {!! Form::url(null, null, ['id' => 'url', 'class' => 'form-control', 'placeholder' => 'https://']) !!}
                                                </div>
                                                <div class="form-group">
                                                    {!! Form::label('linktext', __('Link Text'), ['class' => 'form-label']) !!} <span class="validation-required">*</span>
                                                    {!! Form::text(null, null, ['id' => 'linktext', 'class' => 'form-control', 'placeholder' => __('e.g.: Home')]) !!}
                                                </div>
                                            </div>
                                            @permission('Create LandingPage Menu')
                                            <div class="accordion-body d-flex justify-content-between pt-0">
                                                {!! Form::button(__('Add to Menu'), ['id' => 'add-custom-link', 'class' => 'btn-badge pull-right btn btn-primary btn-sm']) !!}
                                            </div>
                                            @endpermission
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-9 col-lg-7 col-md-6 cat-view">
                                @if ($desiredMenu == '')
                                    <h5>{{ __('Create New Menu') }}</h5>
                                    {!! Form::open(['route' => 'ownermenus.index', 'data-validate', 'novalidate']) !!}
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                {!! Form::label('menu_name', __('Name'), ['class' => 'form-label']) !!} <span class="validation-required">*</span>
                                                {!! Form::text('name', null, [
                                                    'id' => 'menu_name',
                                                    'class' => 'form-control',
                                                    'placeholder' => __('e.g.: Header'),
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="text-right col-12">
                                            {!! Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-sm btn-primary']) !!}
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                                @else

                                    <h4><span>{{ __('Menu Structure') }}</span></h4>
                                    <div id="menu-content">
                                        <div style="min-height: 240px;">
                                            <p>{{ __('Select categories, pages or add custom links to menus.') }}
                                            </p>
                                            @if ($desiredMenu != '')
                                                <ul class="menu ui-sortable" id="menuitems">
                                                    @if (!empty($menuitems))
                                                        @foreach ($menuitems as $key => $item)
                                                            <li data-id="{{ $item->id }}" class="mb-2">

                                                                <div class="accordion accordion-flush" id="">
                                                                    <div class="accordion-item">
                                                                        <h6
                                                                            class="accordion-header mt-2 rounded menu-item-bar">
                                                                            @if (empty($item->name))
                                                                                {{ $item->title }}
                                                                            @else
                                                                                {{ $item->name }}
                                                                            @endif
                                                                            <span class=" according-delete-input"
                                                                                type="button" data-bs-toggle="collapse"
                                                                                data-bs-target="#collapse{{ $item->id . $key }}"
                                                                                aria-expanded="false"
                                                                                aria-controls="pages-list"><i
                                                                                    class="ti ti-arrow-down"></i></span>

                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                                <div class="collapse"
                                                                    id="collapse{{ $item->id . $key }}">
                                                                    <div
                                                                        class="border input-box rounded-bottom border-top-0">
                                                                        {!! Form::open(['route' => ['ownermenus.updateItems', $item->id], 'data-validate', 'novalidate']) !!}
                                                                        <div class="form-group">
                                                                            {!! Form::label('link_name' . $item->id, __('Link Name'), ['class' => 'form-label']) !!}
                                                                            {!! Form::text('title', $item->title, ['id' => 'link_name' . $item->id, 'class' => 'form-control']) !!}
                                                                        </div>
                                                                        @if ($item->type == 'custom')
                                                                            <div class="form-group">
                                                                                {!! Form::label('link_url' . $item->id, __('URL'), ['class' => 'form-label']) !!}
                                                                                {!! Form::url('slug', $item->slug, ['id' => 'link_url' . $item->id, 'class' => 'form-control']) !!}
                                                                            </div>
                                                                        @endif
                                                                        <div class="form-group">
                                                                            <div
                                                                                class="custom-control custom-checkbox d-inline">
                                                                                {!! Form::checkbox('target', '_blank', $item->target == '_blank' ? true : null, [
                                                                                    'id' => 'link_target' . $item->id,
                                                                                    'class' => 'custom-control-input',
                                                                                ]) !!}
                                                                                {!! Form::label('link_target' . $item->id, __('Open in a new tab'), ['class' => 'custom-control-label']) !!}
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn-badge btn btn-sm btn-primary']) !!}
                                                                            <a href="{{ route('ownermenus.deleteItems', [$item->id, $key]) }}"
                                                                                class="mx-1 btn btn-sm btn-danger btn-badge">{{ __('Delete') }}</a>
                                                                        </div>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                </div>
                                                                <ul>
                                                                    @if (isset($item->children))
                                                                        @foreach ($item->children as $m)
                                                                            @foreach ($m as $in => $data)
                                                                                <li data-id="{{ $data->id }}"
                                                                                    class="mb-2">
                                                                                    <div class="accordion accordion-flush"
                                                                                        id="">
                                                                                        <div class="accordion-item">
                                                                                            <h6
                                                                                                class="accordion-header mt-2 rounded menu-item-bar">
                                                                                                @if (empty($data->name))
                                                                                                    {{ $data->title }}
                                                                                                @else
                                                                                                    {{ $data->name }}
                                                                                                @endif
                                                                                                <span
                                                                                                    class=" according-delete-input"
                                                                                                    type="button"
                                                                                                    data-bs-toggle="collapse"
                                                                                                    data-bs-target="#collapse{{ $data->id . $in }}"
                                                                                                    aria-expanded="false"
                                                                                                    aria-controls="pages-list"><i
                                                                                                        class="ti ti-arrow-down"></i></span>

                                                                                            </h6>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="collapse"
                                                                                        id="collapse{{ $data->id . $in }}">
                                                                                        <div
                                                                                            class="border input-box rounded-bottom border-top-0">
                                                                                            {!! Form::open(['route' => ['ownermenus.updateItems', $data->id], 'data-validate', 'novalidate']) !!}
                                                                                            <div class="form-group">
                                                                                                {!! Form::label('link_name' . $data->id, __('Link Name'), ['class' => 'form-label']) !!}
                                                                                                {!! Form::text('title', $data->title, ['id' => 'link_name' . $data->id, 'class' => 'form-control']) !!}
                                                                                            </div>
                                                                                            @if ($data->type == 'custom')
                                                                                                <div class="form-group">
                                                                                                    {!! Form::label('link_url' . $data->id, __('URL'), ['class' => 'form-label']) !!}
                                                                                                    {!! Form::url('slug', $data->slug, ['id' => 'link_url' . $data->id, 'class' => 'form-control']) !!}
                                                                                                </div>
                                                                                            @endif
                                                                                            <div class="form-group">
                                                                                                <div
                                                                                                    class="custom-control custom-checkbox d-inline">
                                                                                                    {!! Form::checkbox('target', '_blank', $data->target == '_blank' ? true : null, [
                                                                                                        'id' => 'link_target' . $data->id,
                                                                                                        'class' => 'custom-control-input',
                                                                                                    ]) !!}
                                                                                                    {!! Form::label('link_target' . $data->id, __('Open in a new tab'), ['class' => 'custom-control-label']) !!}
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="form-group">
                                                                                                {!! Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn-badge btn btn-sm btn-primary']) !!}
                                                                                                <a href="{{ route('ownermenus.deleteItems', [$data->id, $key]) }}"
                                                                                                    class="btn btn-sm btn-danger btn-badge mx-1">{{ __('Delete') }}</a>
                                                                                            </div>
                                                                                            {!! Form::close() !!}
                                                                                        </div>
                                                                                    </div>
                                                                                </li>
                                                                            @endforeach
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            @endif
                                        </div>
                                        <style>
                                            .menu-item-bar {
                                                background: #eee;
                                                padding: 5px 10px;
                                                border: 1px solid #d7d7d7;
                                                margin-bottom: 5px;
                                                width: 75%;
                                                cursor: move;
                                                display: flex;
                                                align-items: center;
                                                justify-content: space-between;
                                                padding: 15px !important;
                                            }
                                            .flex-1{
                                                flex: 1;
                                            }

                                            .menu-item-bar .according-delete-input {
                                                padding: 0 !important;
                                            }

                                            #serialize_output {
                                                display: none;
                                            }

                                            body.dragging,
                                            body.dragging * {
                                                cursor: move !important;
                                            }

                                            .dragged {
                                                position: absolute;
                                                z-index: 1;
                                            }

                                            ol.example li.placeholder {
                                                position: relative;
                                            }

                                            ol.example li.placeholder:before {
                                                position: absolute;
                                            }

                                            #menuitem {
                                                list-style: none;
                                            }

                                            #menuitem ul {
                                                list-style: none;
                                            }

                                            .input-box {
                                                width: 75%;
                                                background: #fff;
                                                padding: 10px;
                                                box-sizing: border-box;
                                                margin-bottom: 5px;
                                            }
                                        </style>
                                        @if ($desiredMenu != '')
                                            <div class="d-flex gap-2">
                                                @permission('Delete Menu')
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['ownermenus.destroy', $desiredMenu->id], 'class' => 'm-0']) !!}
                                                <button type="button" class="btn btn-sm btn-danger show_confirm" data-confirm="{{ __('Are You Sure?') }}"
                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-text-yes="{{ __('Yes') }}" data-text-no="{{ __('No') }}" >
                                                    {{ __(' Delete Menu') }}
                                                </button>
                                                {!! Form::close() !!}
                                                 @endpermission
                                                @permission('Edit Menu')
                                                {!! Form::button(__('Save Menu'), ['id' => 'saveMenu', 'class' => 'btn btn-sm btn-primary']) !!}
                                                @endpermission
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="serialize_output" class="d-none">
        @if ($desiredMenu)
            {{ $desiredMenu->content }}
        @endif
    </div>
@endsection
<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
<script src="{{ asset('js/jquery-sortable-min.js') }}"></script>

@if ($desiredMenu)
    <script>
        $(document).ready(function() {
            $('#select-all-pages').click(function(event) {
                if (this.checked) {
                    $('#pages-list :checkbox').each(function() {
                        this.checked = true;
                    });
                } else {
                    $('#pages-list :checkbox').each(function() {
                        this.checked = false;
                    });
                }
            });
            $('#add-pages').click(function() {
                var menuid = '{{ $desiredMenu->id }}';
                var n = $('input[name="select-page[]"]:checked').length;
                var array = $('input[name="select-page[]"]:checked');
                var ids = [];
                for (i = 0; i < n; i++) {
                    ids[i] = array.eq(i).val();
                }
                if (ids.length == 0) {
                    return false;
                }
                $.ajax({
                    type: "get",
                    data: {
                        menuid: menuid,
                        ids: ids
                    },
                    url: "{{ route('ownermenus.addPage') }}",
                    success: function(res) {
                        if (res.status) {
                            show_toastr('{{ __('Success') }}', res.message, 'success')
                        } else {
                            show_toastr('{{ __('Error') }}', res.message, 'error')
                        }
                        location.reload();
                        $('#loader').fadeOut();
                    }
                })
            })
            $("#add-custom-link").click(function() {
                var menuid = '{{ $desiredMenu->id }}';
                var url = $('#url').val();
                var link = $('#linktext').val();
                if (url.length > 0 && link.length > 0) {
                    $.ajax({
                        type: "get",
                        data: {
                            menuid: menuid,
                            url: url,
                            link: link
                        },
                        url: "{{ route('ownermenus.addLink') }}",
                        success: function(res) {
                            if (res.status) {
                                show_toastr('{{ __('Success') }}', res.message, 'success')
                            } else {
                                show_toastr('{{ __('Error') }}', res.message, 'error')
                            }
                            location.reload();
                            $('#loader').fadeOut();
                        }
                    })
                }
            })
            $('#saveMenu').click(function() {
                var menuid = '{{ $desiredMenu->id }}';
                var location = $('input[name="location"]:checked').val();
                var newText = $("#serialize_output").text();
                var data = JSON.parse($("#serialize_output").text());
                $.ajax({
                    type: "get",
                    data: {
                        menuid: menuid,
                        data: data,
                        location: location
                    },
                    url: "{{ route('ownermenus.update') }}",
                    success: function(res) {
                        if (res.status) {
                            show_toastr('{{ __('Success') }}', res.message, 'success')
                        } else {
                            show_toastr('{{ __('Error') }}', res.message, 'error')
                        }
                        window.location.reload();
                        $('#loader').fadeOut();
                    }
                })
            })
        });
    </script>
@endif
<script>
    jQuery(document).ready(function($) {
        var group = $("#menuitems").sortable({
            group: 'serialization',
            isValidTarget: function($item, container) {
                var depth = 1, // Start with a depth of one (the element itself)
                    maxDepth = 1,
                    children = $item.find('ol').first().find('li');
                // Add the amount of parents to the depth
                if (container.el.parents('li').length > 1) {
                    depth += 1;
                }
                depth += container.el.parents('ol').length;
                // Increment the depth for each time a child
                while (children.length) {
                    depth++;
                    children = children.find('ol').first().find('li');
                }
                return depth <= maxDepth;
            },
            onDrop: function($item, container, _super) {
                var data = group.sortable("serialize").get();
                var jsonString = JSON.stringify(data, null, ' ');
                $('#serialize_output').text(jsonString);
                _super($item, container);
            }
        });
        setTimeout(() => {
            $('#serialize_output').text(JSON.stringify(group.sortable("serialize").get(), null, ' '));
        }, 300);
    });

    
</script>
