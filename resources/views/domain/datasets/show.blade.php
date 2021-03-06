<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */
/** @var \App\Domain\Dataset\Dataset $dataset */
?>
@extends('layouts.app')
@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <a href="{{ url()->previous() }}" class="btn btn-default m-btn--pill m-btn--air"><i class="fa fa-reply"></i> Back</a>
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    <h3>Dataset: {{ $dataset->getFullName() }}</h3>
                    <a href="{{ route('datasets.download', ['dataset' => $dataset]) }}"
                       class="btn m-btn--pill m-btn--air btn-primary">{{ __('Download') }}</a>

                    @if($dataset->user_id == \Auth::id())
                    <a href="{{ route('datasets.edit', ['dataset' => $dataset]) }}"
                       class="btn m-btn--pill m-btn--air btn-primary">{{ __('Edit') }}</a>
                    @endif
                </h1>
            </div>
        </div>
    </div>
    <!-- END: Subheader -->
    <div class="m-content">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>


        <div class="m-portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text">
                            @lang('Dataset') @lang('data')
                        </h3>
                    </div>
                </div>
            </div>
            <div class="m-portlet__body">
                <!--begin::Section-->
                <div class="m-section">
					{{--<span class="m-section__sub">--}}
					{{--</span>--}}
                    <div class="m-section__content">
                        <table class="table">
                            <thead>
                            <th><strong>@lang('Text')</strong></th>
                            <th><strong>@lang('Category')</strong></th>
                            </thead>
                            <tbody>
                            @foreach($dataset->data()->orderBy('id')->limit(100)->get()->all() as $data)
                                <tr>
                                    <td>{{ $data->text }}</th>
                                    <td>{{ $data->label->text }}</th>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Section-->
            </div>
            <!--end::Form-->
        </div>
    </div>
@endsection


