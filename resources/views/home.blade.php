@extends('layouts.app')

@section('content')
<!-- BEGIN: Subheader -->
<div class="m-subheader ">
    <div class="d-flex align-items-center">
        <div class="mr-auto">
            <h1 class="m-subheader__title">
                {{ __('GraphGrailAi platform') }}
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
                        {{ __('Datasets') }} <a href="{{ route('datasets.create') }}" class="btn m-btn--pill m-btn--air btn-primary">+{{ __('Add') }}</a>
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
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>File</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($datasets as $dataset)
                            <tr>
                                <th scope="row">{{ $dataset->id }}</th>
                                <td><a href="{{ route('datasets.show', ['dataset' => $dataset]) }}">{{ $dataset->name }}</a></td>
                                <td>{{ $dataset->statusLabel() }}</td>
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

    <div class="m-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        {{ __('Ai Models') }} <a href="{{ route('ai-models.create') }}" class="btn m-btn--pill m-btn--air btn-primary">+{{ __('Add') }}</a>
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
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('Dataset')</th>
                            <th>@lang('Status')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($models as $model)
                            <tr>
                                <th scope="row">{{ $model->id }}</th>
                                <td><a href="{{ route('ai-models.show', ['model' => $model]) }}">{{ $model->dataset->name}}</a></td>
                                <td>{{ $model->statusLabel() }}</td>
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
