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
                                <h5>{{ __('Why Choose Section') }}</h5>
                            </div>
                            <div id="p1" class="col-auto text-end text-primary h3">
                                <a image-url="{{ get_file('packages/workdo/LandingPage/src/Resources/assets/infoimages/whychoose.png') }}"
                                data-url="{{ route('info.image.view',['marketplace','whychoose']) }}" class="view-images">
                                    <i class="ti ti-info-circle pointer"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{ Form::open(array('route' => array('whychoose_store',$slug), 'method'=>'post', 'enctype' => "multipart/form-data")) }}
                        <div class="card-body">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('whychoose_heading', __('Heading'), ['class' => 'form-label']) }}
                                        {{ Form::text('whychoose_heading',$settings['whychoose_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading'), 'id' => 'whychoose_heading']) }}
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('whychoose_description', __('Description'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('whychoose_description', $settings['whychoose_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id'=>'whychoose_description','required'=>'required']) }}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row" >
                                        <div class="py-3">
                                            <h5>{{ __('Pricing Plan Section') }}</h5>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('pricing_plan_heading', __('Heading'), ['class' => 'form-label']) }}
                                                {{ Form::text('pricing_plan_heading',$settings['pricing_plan_heading'], ['class' => 'form-control ', 'placeholder' => __('Enter Heading')]) }}
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {{ Form::label('pricing_plan_description', __('Description'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('pricing_plan_description', $settings['pricing_plan_description'], ['class' => 'summernote form-control', 'placeholder' => __('Enter Description'), 'id'=>'pricing_plan_description','required'=>'required']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                {{ Form::label('pricing_plan_demo_link', __('Live Demo button Link'), ['class' => 'form-label']) }}
                                                {{ Form::text('pricing_plan_demo_link',$settings['pricing_plan_demo_link'], ['class' => 'form-control', 'placeholder' => __('Enter Link')]) }}
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                {{ Form::label('pricing_plan_demo_button_text', __('Live Demo Button Text'), ['class' => 'form-label']) }}
                                                {{ Form::text('pricing_plan_demo_button_text',$settings['pricing_plan_demo_button_text'], ['class' => 'form-control', 'placeholder' => __('Enter Button Text')]) }}
                                            </div>
                                        </div>
                                        <div class="border" >
                                            <div class="row py-3 border-bottom">
                                                <div class="col"><h5>{{ __("Plan Features") }}</h5></div>
                                                <div class="col-auto text-end">
                                                    <button id="add-cards-details"
                                                        class="btn btn-sm btn-badge btn-primary btn-icon"
                                                        data-bs-toggle="tooltip" title="{{ __('Add Titles') }}">
                                                        <i class="ti ti-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @if (isset($settings['pricing_plan_text']) && !empty($settings['pricing_plan_text']) )
                                                @foreach (json_decode($settings['pricing_plan_text'] ,true) as $key => $title)
                                                    <div id="{{ 'add-cards'.$key }}" class="border-bottom row py-2">
                                                        <div class="col-10">
                                                            <div class="form-group">
                                                                {{ Form::label('pricing_plan_text'.$key, __('Title'), ['class' => 'form-label']) }}
                                                                {{ Form::text('pricing_plan_text['.$key.'][title]',$title['title'], ['class' => 'form-control','id'=>'pricing_plan_text'.$key, 'placeholder' => __('Enter Title'),'required'=>'required']) }}
                                                            </div>
                                                        </div>
                                                        <div class="col-2 d-flex text-center align-items-center justify-content-end">
                                                            <a href="#" id="{{ 'delete-card'.$key }}" class="card-delete btn btn-badge btn-danger btn-sm bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-title="{{__('Delete')}}" data-original-title="{{__('Delete')}}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div id="add-cards1" class="border-bottom row py-2">
                                                    <div class="col-10">
                                                        <div class="form-group">
                                                            {{ Form::label('pricing_plan_text', __('Title'), ['class' => 'form-label']) }}
                                                            {{ Form::text('pricing_plan_text[1][title]',null, ['class' => 'form-control', 'placeholder' => __('Enter Title'),'required'=>'required']) }}
                                                        </div>
                                                    </div>
                                                    <div class="col-2 d-flex text-center align-items-center justify-content-end">
                                                        <a href="#" id="{{ 'delete-card1' }}" class="card-delete btn btn-danger btn-badge btn-sm bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-title="{{__('Delete')}}" data-original-title="{{__('Delete')}}">
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice btn-badge btn-primary mr-2" type="submit" value="{{ __('Save Changes') }}">
                        </div>
                    {{ Form::close() }}
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
@push('scripts')
    <script>
        $("#add-cards-details").click(function(e){
            e.preventDefault()

        // get the last DIV which ID starts with ^= "another-participant"
        var $div = $('div[id^="add-cards"]:last');

        // Read the Number from that DIV's ID (i.e: 1 from "another-participant1")
        // And increment that number by 1
        var num = parseInt( $div.prop("id").match(/\d+/g), 10 ) +1;

        // Clone it and assign the new ID (i.e: from num 4 to ID "another-participant4")
        var $klon = $div.clone().prop('id', 'add-cards'+num );

        $klon.find('a').each(function() {
            this.id = "delete-card"+num;
        });

        // for each of the inputs inside the div, clear it's value and
        // increment the number in the 'name' attribute by 1
        $klon.find('input').each(function() {
            this.value= "";
            let name_number = this.name.match(/\d+/);
            name_number++;
            this.name = this.name.replace(/\[[0-9]\]+/, '['+name_number+']')

            let id_number = this.id.match(/\d+$/);
            id_number++;
            this.id = this.id.replace(/\d+$/, id_number);

        });
        // Finally insert $klon after the last div
        $div.after( $klon );
        });

        $(document).on('click', '.card-delete', function(e) {
            e.preventDefault()
            var id = $(this).attr('id');
            var num = parseInt( id.match(/\d+/g), 10 );
            var card = document.getElementById("add-cards"+num);
            if(num != 1){
                card.remove();
            }
        });
    </script>
@endpush
