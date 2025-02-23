@php
$adminSetting = getSuperAdminAllSetting();
config([
    'captcha.secret' => $adminSetting['NOCAPTCHA_SECRET'] ?? '',
    'captcha.sitekey' => $adminSetting['NOCAPTCHA_SITEKEY'] ?? '',
    'options' => [
        'timeout' => 30,
    ],
]);
@endphp

    @extends('layouts.guest')

    @section('page-title')
        {{ __('Reset Password') }}
    @endsection
    @if (isset($adminSetting['cust_darklayout']) && $adminSetting['cust_darklayout'] == 'on')
        <style>
            .g-recaptcha {
                filter: invert(1) hue-rotate(180deg) !important;
            }
        </style>
    @endif

    @section('content')
        <div class="">
            <h2 class="mb-3 f-w-600">{{__('Reset Password')}}</h2>
        </div>
        <div class="">
            <!-- Validation Errors -->
            <x-auth-validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('password.store') }}">
                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                @csrf

                <div class="form-group mb-3">
                    <label class="form-label" for="email">{{ __('Email') }}</label>
                    <x-input id="email" class="form-control" type="email" name="email" :value="old('email', $request->email)" required
                    placeholder="{{ __('Enter Email') }}" autofocus />
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="password">{{ __('Password') }}</label>
                    <x-input id="password" class="form-control" type="password" name="password"  placeholder="{{ __('Enter Password') }}" required />
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                    <x-input id="password_confirmation" class="form-control" type="password" name="password_confirmation"
                    placeholder="{{ __('Enter Confirm Password') }}" required />
                </div>

                @if (isset($adminSetting['RECAPTCHA_MODULE']) && $adminSetting['RECAPTCHA_MODULE'] == 'yes')
                    @if (isset($adminSetting['NOCAPTCHA_VERSON']) && $adminSetting['NOCAPTCHA_VERSON'] == 'v2')
                        <div class="form-group col-lg-12 col-md-12 mt-3">
                            {!! NoCaptcha::display((isset($adminSetting['cust_darklayout']) && $adminSetting['cust_darklayout'] == 'on') ? ['data-theme' => 'dark'] : []) !!}
                            @error('g-recaptcha-response')
                                <span class="small text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @else
                        <div class="form-group col-lg-12 col-md-12 mt-3">
                            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" class="form-control">
                            @error('g-recaptcha-response')
                                <span class="error small text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @endif
                @endif

                <div class="d-grid">
                    {!! Form::hidden('type', 'admin') !!}
                    <button class="btn btn-primary btn-block mt-2" type="submit">
                        {{ __('Reset Password') }}
                    </button>
                </div>

            </form>
        </div>
    @endsection

@push('scripts')
    @if (isset($adminSetting['RECAPTCHA_MODULE']) && $adminSetting['RECAPTCHA_MODULE'] == 'yes')
        @if (isset($adminSetting['NOCAPTCHA_VERSON']) && $adminSetting['NOCAPTCHA_VERSON'] == 'v2')
                {!! NoCaptcha::renderJs() !!}
        @else
            <script src="https://www.google.com/recaptcha/api.js?render={{ $adminSetting['NOCAPTCHA_SITEKEY'] }}"></script>
            <script>
                $(document).ready(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ $adminSetting['NOCAPTCHA_SITEKEY'] }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
                        });
                    });
                });
            </script>
        @endif
    @endif
@endpush