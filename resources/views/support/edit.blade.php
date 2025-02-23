@extends('layouts.app')

@section('page-title', __('Reply Ticket'))


@section('action-button')
    <div class=" text-end d-flex all-button-box justify-content-md-end justify-content-center">
        <a href="#" onclick="saveAsPDF();" id="download-buttons" class="btn btn-sm btn-primary btn-icon d-flex align-items-center"
            data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Print')}}" aria-label="Print">
            <i class="ti ti-printer" style="font-size:20px"></i>
        </a>
        @php
            $btn_class = 'btn-info';
            if($ticket->status == 'open') {
                $btn_class = 'btn-info';
            } else {
                $btn_class = 'btn-success';
            }
        @endphp
        <div class="btn-group mx-1" id="deliver_btn">
            <button class="btn {{ $btn_class }} {{ in_array($ticket->status, ['open', 'In Progress','solved']) ? 'dropdown-toggle' : '' }} order_status_btn" type="button"
                {{ in_array($ticket->status, ['Solved']) ? 'data-bs-toggle="dropdown"' : "" }}
                    data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="true">{{ __('Status') }} : {{ $ticket->status }}
            </button>
            @if(in_array($ticket->status, ['open', 'In Progress','solved']))
                <div class="dropdown-menu" data-popper-placement="bottom-start">
                    <h6 class="dropdown-header">{{ __('Set Ticket status') }}</h6>
                    @if($ticket->status == 'open')
                        <a class="dropdown-item ticket_status" href="#" data-value="In Progress">
                            <i class="fa fa-check text-success"></i> {{ __('In Progress') }}
                        </a>
                        <a class="dropdown-item ticket_status" href="#" data-value="Solved">
                            <i class="fa fa-check text-success"></i> {{ __('Solved') }}
                        </a>
                    @endif
                    @if($ticket->status == 'In Progress')
                        <a class="dropdown-item ticket_status" href="#" data-value="Solved">
                            <i class="fa fa-check text-success"></i> {{ __('Solved') }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('support_ticket.index') }}">{{ __('Support Ticket') }}</a></li>
    <li class="breadcrumb-item"> {{ __('Reply Ticket') }} - {{ $ticket->ticket_id }}</li>
@endsection
@php
    $logo = get_file('/');
@endphp
@section('content')
    <div class="row mt-3">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="printableArea">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h6>
                            <span class="text-left">
                                {{ $ticket->UserData->name }} <small>({{ $ticket->created_at ? $ticket->created_at->diffForHumans() : '-'}})</small>
                                <span class="d-block"><small></small></span>
                            </span>
                        </h6>
                        <span class="text-right">
                            {{ __('Status') }} : <span class="badge rounded p-2 @if($ticket->status == 'New Ticket') bg-secondary @elseif($ticket->status == 'In Progress')bg-info  @elseif($ticket->status == 'On Hold') bg-warning @elseif($ticket->status == 'Closed') bg-primary @else bg-success @endif">{{ __($ticket->status) }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <span><b>{{ $ticket->title }}</b></span>
                        <p>{!! $ticket->description !!}</p>
                    </div>
                    @php $attachments = json_decode($ticket->attachment); @endphp

                        @if (!empty($attachments) && count($attachments))
                            <div class="m-1">
                                <h6>{{ __('Attachments') }} :</h6>

                                <ul class="list-group list-group-flush">

                                    @foreach ($attachments as $index => $attachment)
                                        <li class="list-group-item px-0">
                                            {{ $attachment }} <a download="" href="{{ get_file($attachment,APP_THEME()) }}" class="edit-icon py-1 ml-2" title="{{ __('Download') }}"><i class="fas fa-download ms-2"></i></a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                </div>
            </div>

            @foreach ($ticket->conversions as $conversion)
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $conversion->replyBy()->name }}
                            <small>({{ $conversion->created_at->diffForHumans() }})</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div>{!! $conversion->description !!}</div>
                        @php $attachments = json_decode($conversion->attachments); @endphp
                        @if (!empty($attachments) && count($attachments))
                            <div class="m-1">
                                <h6>{{ __('Attachments') }} :</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach ($attachments as $index => $attachment)
                                    <li class="list-group-item px-0">
                                        {{ $attachment }}<a download="" href="{{ get_file($attachment,APP_THEME()) }}" class="edit-icon py-1 ml-2" title="{{ __('Download') }}"><i class="fa fa-download ms-2"></i></a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    @if($ticket->status != 'Closed')
                    <div class="card">
                        <div class="card-header row">
                            <div class="col-md-4">
                                <h6>{{ __('Add Reply') }}</h6>
                            </div>
                        </div>

                        <form method="post" action="{{ route('conversion.store', $ticket->id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="form-group col-md-7">
                                    {!! Form::label('', __('Description'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('reply_description', old('reply_description'), ['class' => 'form-control  summernote-simple-product', 'rows' => 1, 'id' => 'description']) !!}
                                </div>
                                <div class="form-group file-group mb-5 col-md-7">
                                    <label class="require form-label">{{ __('Attachments') }}</label>
                                    <label class="form-label"><small>({{ __('You can select multiple files') }})</small></label>
                                    <div class="choose-file form-group">
                                        <label for="file" class="form-label d-block">
                                            <div>{{ __('Choose File Here') }}</div>

                                            <input type="file" name="reply_attachments[]" id="file" class="form-control mb-2 {{ $errors->has('reply_attachments') ? ' is-invalid' : '' }}" multiple=""  data-filename="multiple_reply_file_selection" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                            <img src="" id="blah" width="20%"/>
                                            <div class="invalid-feedback">
                                                {{ $errors->first('reply_attachments.*') }}
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <p class="multiple_reply_file_selection"></p>
                                <div class="text-end">
                                    <button class="btn btn-primary btn-block mt-2 btn-submit btn-badge" type="submit">{{ __('Submit') }}</button>
                                </div>
                            </div>

                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-script')
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}{{ '?' . time() }}"></script>
    <script>
        var filename ='{{$ticket->ticket_id}}';
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();

        }

    </script>
    <script>

        $(document).on('click', '.ticket_status', function() {
            var status = $(this).attr('data-value');
            var data = {
                status: status,
                id: "{{ $ticket->id }}",
            };

            $.ajax({
                url: '{{ route('support_ticket.status.change', $ticket->id) }}',
                method: 'POST',
                data: data,
                context: this,
                success: function(data) {
                    $('#loader').fadeOut();
                    if (data.status == 'error') {
                        show_toastr('{{ __('Error') }}', data.message, 'error');
                    } else {
                        var newStatusText = data.ticket_status;
                        $('.order_status_btn').text('{{ __('Status') }} : ' + newStatusText);
                    }
                },
                complete: function() {
                    $('#loader').fadeOut();
                    show_toastr('{{ __('Success') }}', '{{ __('Ticket status updated successfully') }}', 'success');
                }
            });
        });
    </script>

@endpush
