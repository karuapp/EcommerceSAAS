{{Form::model($subCategory, array('route' => array('sub-category.update', $subCategory->id), 'method' => 'PUT', 'enctype' => 'multipart/form-data')) }}
<div class="row">
    <div class="form-group  col-md-12">
        {!! Form::label('sub-category-title', __('Title'), ['class' => 'form-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'sub-category-title']) !!}
    </div>
    <div class="form-group  col-md-12">
        {!! Form::label('', __('Category'), ['class' => 'form-label']) !!}
        {!! Form::select('maincategory_id', $MainCategoryList, null, ['class' => 'form-control', 'data-role' => 'tagsinput', 'id' => 'category_id']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('', __('Image'), ['class' => 'form-label']) !!}

        <div class="row">
            <div class="col-md-12">
            <label for="upload_image">
                <div class="image-upload bg-primary pointer w-100 logo_update"> <i
                        class="ti ti-upload px-1"></i>{{ __('Choose File Here') }}
                </div>
                <input type="file" class="form-control file d-none"
                    name="image" id="upload_image"
                    data-filename="logo_update"
                    onchange="document.getElementById('categoryImage').src = window.URL.createObjectURL(this.files[0])">
            </label>
            </div>
            <div class="logo-content mt-3 col-md-12">
                    <img src="{{ get_file($subCategory->image_path, APP_THEME()) ?? '#' }}"
                        class="big-logo invoice_logo img_setting" id="categoryImage" width="200px">
            </div>
        </div>

    </div>
    <div class="form-group col-md-6">
        {!! Form::label('', __('Icon'), ['class' => 'form-label']) !!}
        <div class="row">
            <div class="col-md-12">
            <label for="upload_icon_image">
                <div class="image-upload bg-primary pointer w-100 logo_update"> <i
                        class="ti ti-upload px-1"></i>{{ __('Choose File Here') }}
                </div>
                <input type="file" class="form-control file d-none"
                    name="icon_path" id="upload_icon_image"
                    data-filename="logo_update"
                    onchange="document.getElementById('categoryIcon').src = window.URL.createObjectURL(this.files[0])">
            </label>
            </div>
            <div class="logo-content mt-3 col-md-12">
                    <img src="{{ get_file($subCategory->icon_path, APP_THEME()) ?? '#' }}"
                        class="big-logo invoice_logo img_setting" id="categoryIcon" width="200px">
            </div>
        </div>
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            {!! Form::checkbox('status', 1, null, [
                'class' => 'form-check-input status',
                'id' => 'status',
            ]) !!}
            <label class="form-check-label" for="status"></label>
        </div>
    </div>
    <div class="modal-footer pb-0">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-badge btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-badge btn-primary mx-1">
    </div>
</div>
{!! Form::close() !!}
