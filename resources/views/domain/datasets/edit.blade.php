<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\Dataset\Dataset $dataset */
//$url = $dataset->id ? url('datasets') : url('datasets');
$method = $dataset->id ? 'put' : 'post';
$route = $dataset->id ? route('datasets.update', ['dataset' => $dataset]) : route('datasets.store');

$langs = [
    'ru' => 'Rus',
    'en' => 'Eng',
];

?>

@extends('layouts.app')
@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h3>Upload dataset .csv-file</h3>
            </div>
        </div>
    </div>
    <!-- END: Subheader -->
    <div class="m-content">
        <div class="m-portlet m-portlet--tab">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
						<span class="m-portlet__head-icon m--hide">
						<i class="la la-gear"></i>
						</span>
                        <h3 class="m-portlet__head-text">
                            Choose csv-file
                        </h3>
                    </div>
                </div>
            </div>
            <!--begin::Form-->
            {!! Form::model($dataset, [
                'url' => $route, 'files' => true,
                'class' => 'm-form m-form--fit m-form--label-align-right',
            ]); !!}
            @method($method)
                <div class="m-portlet__body">
                    @if ($errors->any())
                        <div class="form-group m-form__group m--margin-top-10">
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-danger alert-dismissible fade show   m-alert m-alert--air" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    </button>
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group m-form__group">
                        {{ \Form::label('name', __('Name')) }}
                        {{ \Form::text('name', $dataset->name ?? '', ['class' => 'form-control m-input m-input--air']) }}
                    </div>

                    <div class="form-group m-form__group">
                        {{ \Form::label('lang', __('Language')) }}
                        {{ \Form::select('lang', $langs, $dataset->lang ?? 'ru', ['class' => 'form-control m-input m-input--air']) }}
                    </div>
                </div>
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions">
                        {!! Form::submit('Save', ['class' => 'btn m-btn--pill m-btn--air btn-success']) !!}
                    </div>
                </div>
            {!! Form::close() !!}
            <!--end::Form-->
        </div>
    </div>
@endsection



