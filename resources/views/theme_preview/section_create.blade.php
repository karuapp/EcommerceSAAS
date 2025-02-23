{{ Form::open(['route' => 'theme-section.store', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}

<div class="row">
    <div class="form-group col-md-12">
        <input type="hidden" name="theme_id" value="{{ $theme_id }}">
        {!! Form::label('', __('Section Name'), ['class' => 'form-label']) !!}
        {!! Form::text('section_name', null, ['class' => 'form-control']) !!}
    </div>
    <div class="modal-footer pb-0">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-badge btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-badge btn-primary mx-1">
    </div>
</div>
{!! Form::close() !!}
