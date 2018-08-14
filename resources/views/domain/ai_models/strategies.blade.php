<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\Strategy\StrategyProvider $provider */
?>
@extends('layouts.app')

@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    <h3>Select AI Provider</h3>
                </h1>
            </div>
        </div>
    </div>
    <!-- END: Subheader -->


    <div class="m-content">
        <div class="form-group m-form__group m--margin-top-10">
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger m-alert m-alert--default" role="alert">
                        {{ $error }}
                    </div>
                @endforeach
            @endif
        </div>

        @foreach($provider->all() as $strategy)
            <div class="m-portlet m-portlet--tab">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
						<span class="m-portlet__head-icon m--hide">
						<i class="la la-gear"></i>
						</span>
                            <h3 class="m-portlet__head-text">
                                "{{ $strategy->name() }}"
                            </h3>
                        </div>
                    </div>
                </div>
                <!--begin::Form-->
                <form class="col-md-8 m-form m-form--fit m-form--label-align-right" method="POST" action="{{ route('ai-models.create') }}">
                    @method('GET')
                    @csrf
                    <input type="hidden" name="strategy" value="{{ $strategy->getFormName() }}">

                    <div class="m-portlet__foot m-portlet__foot--fit m-portlet__no-border">
                        <div class="m-form__actions">
                            <button type="submit" class="btn btn-accent">{{ __('Select') }}</button>
                        </div>
                    </div>
                </form>
                <!--end::Form-->
            </div>
        @endforeach
    </div>
@endsection
