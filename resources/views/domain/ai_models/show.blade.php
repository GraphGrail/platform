<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */
/** @var \App\Domain\AiModel $model */
?>
@extends('layouts.app')
@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    Ai Model: {{ $model->id }}{{ $model->dataset ? '-' . $model->dataset->name : '' }}. {{ $model->statusLabel() }}
                </h1>
            </div>
            @if ($model->dataset)
                <a href="{{ route('datasets.download', ['dataset' => $model->dataset]) }}"
                   class="btn m-btn--pill m-btn--air btn-primary">{{ __('Download') }}</a>
                <a class="btn m-btn--pill m-btn--air btn-warning m-btn--wide" href="{{ route('ai-models.edit', ['model' => $model]) }}">Edit</a>
            @else
                <a class="btn m-btn--pill m-btn--air btn-warning m-btn--wide" href="{{ route('ai-models.edit', ['model' => $model]) }}">Empty dataset</a>
            @endif
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
        @if($model->status === \App\Domain\AiModel::STATUS_NEW)
            <div class="m-portlet" id="m_blockui_2_portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">
                                Train model
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin::Section-->
                    <div class="m-section">
                    <span class="m-section__sub">

                    </span>
                        <div class="m-section__content">
                            <form method="POST" action="{{ url('ai-models/train', ['model' => $model]) }}">
                                @csrf
                                <input type="hidden" name="model" value="{{ $model->id }}">
                                <input type="hidden" name="dataset" value="{{ $model->dataset ? $model->dataset->id : '' }}">
                                <button class="btn btn-accent " type="submit">Start</button>
                            </form>
                        </div>
                    </div>
                    <!--end::Section-->

                    <div class="m-separator m-separator--dashed"></div>
                </div>
            </div>
        @endif
        @if($model->status === \App\Domain\AiModel::STATUS_READY)
            <div class="m-portlet" id="m_blockui_2_portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">
                                Your credentials for api-requests
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin::Section-->
                    <div class="m-section">
                    <span class="m-section__sub">

                    </span>
                        <div class="m-section__content">
                            <div class="alert m-alert--default" role="alert">
                                <strong>Url:</strong> {{ route('api.exec', ['model' => $model]) }}
                            </div>
                            <div class="alert m-alert--default" role="alert">
                                <strong>Bearer Token:</strong> {{ Auth::user()->api_token }}
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->

                    <div class="m-separator m-separator--dashed"></div>
                </div>
            </div>
        @endif
    </div>
@endsection
