{{ Form::open(['route' => 'state.store', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="row">
<input type="hidden" name="country_active_tab" value="pills-state-tab">
    <div class="form-group col-md-12">
        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control font-style', 'placeholder' => 'Enter State Name', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('country_id', __('Country'), ['class' => 'form-label']) !!}
        {!! Form::select('country_id', $countries, null, [
            'class' => 'form-control',
            'id' => 'country_id',
            'placeholder' => 'Select Option',
        ]) !!}
    </div>
    <div class="modal-footer pb-0">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-badge btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-badge btn-primary mx-1">
    </div>
</div>
{!! Form::close() !!}
