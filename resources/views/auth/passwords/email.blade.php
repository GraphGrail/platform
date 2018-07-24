@extends('layouts.unauth.app')

@section('content')
    <div class="m-login__container">
        <div class="m-login__logo">
            <a href="#">
                <img title="GraphGrailAi" src="/images/logo.png">
            </a>
        </div>

        <div class="m-login__forget-password" style="display: block">
            <div class="m-login__head">
                <h3 class="m-login__title">
                    {{ __('Reset Password') }}
                </h3>
                <div class="m-login__desc">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
            <form class="m-login__form m-form" method="POST" action="{{ route('password.email') }}" aria-label="{{ __('Reset Password') }}">
                @csrf
                <div class="form-group m-form__group">
                    <input id="email" type="email" class="m-input form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required placeholder="{{ __('E-Mail Address') }}">
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                    @endif
                </div>
                <div class="m-login__form-action">
                    <button type="submit" id="m_login_forget_password_submit" class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air  m-login__btn m-login__btn--primaryr">
                        {{ __('Send Password Reset Link') }}
                    </button>
                    &nbsp;&nbsp;
                    <a href="{{ route('login') }}" id="m_login_forget_password_cancel" class="btn btn-outline-focus m-btn m-btn--pill m-btn--custom m-login__btn">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
