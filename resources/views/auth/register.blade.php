@extends('layouts.unauth.app')

@section('content')
    <div class="m-login__container">
        <div class="m-login__logo">
            <a href="#">
                <img title="GraphGrailAi" src="images/logo.png">
            </a>
        </div>
        <div class="m-login__signup" style="display: block">
            <div class="m-login__head">
                <h3 class="m-login__title">
                    {{ __('Register') }}
                </h3>
                <div class="m-login__desc">
                    Enter your details to create your account:
                </div>
            </div>
            <form class="m-login__form m-form" method="POST" action="{{ route('register') }}" aria-label="{{ __('Register') }}">
                @csrf
                <div class="form-group m-form__group">
                    <input id="name" type="text" class="m-input form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" placeholder="{{ __('Name') }}" required autofocus>
                    @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group m-form__group">
                    <input id="email" type="email" class="m-input form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" placeholder="{{ __('E-Mail Address') }}" required>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group m-form__group">
                    <input id="password" type="password" class="m-input form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ __('Password') }}" required>

                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group m-form__group">
                    <input id="password-confirm" type="password" class="form-control m-input m-login__form-input--last" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" required>
                </div>
                <div class="m-login__form-action">
                    <button type="submit" id="m_login_signup_submit" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air  m-login__btn">
                        {{ __('Register') }}
                    </button>
                    &nbsp;&nbsp;
                    <a href="{{ route('login') }}" id="m_login_signup_cancel" class="btn btn-outline-focus m-btn m-btn--pill m-btn--custom  m-login__btn">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
