@extends('layouts.unauth.app')

@section('content')
    <div class="m-login__container">
        <div class="m-login__logo">
            <a href="#">
                <img title="GraphGrailAi" src="images/logo.png">
            </a>
        </div>
        <div class="m-login__signin">
            <div class="m-login__head">
                <h3 class="m-login__title">
                    {{ __('Login') }}
                </h3>
            </div>
            <form class="m-login__form m-form" method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                @csrf

                <div class="form-group m-form__group">
                    <input class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }} m-input" type="text"
                           placeholder="{{ __('E-Mail Address') }}" name="email" value="{{ old('email') }}"
                           autocomplete="off">
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group m-form__group">
                    <input class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }} m-input m-login__form-input--last"
                           type="password" placeholder="{{ __('Password') }}" name="password">
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="row m-login__form-sub">
                    <div class="col m--align-left m-login__form-left">
                        <label class="m-checkbox  m-checkbox--focus">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            {{ __('Remember Me') }}
                            <span></span>
                        </label>
                    </div>
                    <div class="col m--align-right m-login__form-right">
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    </div>
                </div>
                <div class="m-login__form-action">
                    <button type="submit" id="m_login_signin_submit"
                            class="btn btn-focus m-btn m-btn--pill m-btn--custom m-btn--air m-login__btn m-login__btn--primary">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>
        </div>
        <div class="m-login__account">
        <span class="m-login__account-msg">
            Don't have an account yet ?
        </span>&nbsp;&nbsp;
            <a href="{{ route('register') }}" id="m_login_signup" class="m-link m-link--light m-login__account-link">
                {{ __('Register') }}
            </a>
        </div>
    </div>
@endsection
