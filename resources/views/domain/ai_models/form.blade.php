<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\AiModel $model */
/** @var \App\Domain\Strategy\Strategy $strategy */
/** @var \App\Domain\Dataset\Dataset[] $datasets */

$configuration = $model->configuration;
$method = $model->id ? 'PUT' : 'POST';

$route = $model->id ? route('ai-models.update', ['model' => $model]) : route('ai-models.store');

$datasets = collect($datasets)->mapWithKeys(function (\App\Domain\Dataset\Dataset $d) {
    return [$d->id => $d->name];
})->prepend('None', 0)->all();

$selected = [];
$available = [];

foreach ($strategy->getComponents() as $component) {
    if ($component->optional) {
        $available[] = $component;
        continue;
    }
    $selected[] = $component;
}

if ($model->configuration) {
    $selected = $model->configuration->components();
    foreach ($selected as $component) {
        if (!$component->optional) {
            continue;
        }
        foreach ($available as $k => $item) {
            if ($item::name() === $component::name()) {
                unset($available[$k]);
            }
        }
    }
}
?>
@extends('layouts.app')

@section('styles')
    <link href="{{ asset('css/ui.css') }}" rel="stylesheet">
@endsection
@section('scripts')
    <script src="{{ asset('js/ui.js') }}" defer></script>
@endsection

@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    <h3>Ai model {{ $model->getFullName() }}</h3>
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

        <div class="m-portlet m-portlet--tab">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
						<span class="m-portlet__head-icon m--hide">
						<i class="la la-gear"></i>
						</span>
                        <h3 class="m-portlet__head-text">
                            Create model "{{ $strategy->name() }}"
                        </h3>
                    </div>
                </div>
            </div>

            <div class="m-portlet__body m-portlet__body--modified">
                <div class="m-portlet__body-left">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <span class="m-portlet__head-icon m--hide">
                                    <i class="la la-gear"></i>
                                </span>
                                <h5 class="m-portlet__head-text">
                                    {{ __('Component Palette') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="ui-sortable left-sortable prepare-drop">
                        @foreach($available as $component)
                            @include('domain.ai_models.component', ['component' => $component])
                        @endforeach
                    </div>
                </div>
                <div class="m-portlet__body-right">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <span class="m-portlet__head-icon m--hide">
                                    <i class="la la-gear"></i>
                                </span>
                                <h5 class="m-portlet__head-text">
                                    {{ __('Customize Solution') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <!--begin::Form-->
                    <form class="col-md-12 m-form m-form--fit m-form--label-align-right" method="POST" action="{{ $route }}">
                        @method($method)
                        @csrf
                        <div class="form-group m-form__group">
                            {{ \Form::label('name', __('Name')) }}
                            {{ \Form::text('name', $model->name ?? '', ['class' => 'form-control m-input m-input--air']) }}
                        </div>
                        <input type="hidden" name="strategy" value="{{ $strategy->getFormName() }}">
                        <div class="form-group m-form__group">
                            {{ \Form::label('dataset', 'Dataset') }}
                            {{ \Form::select('dataset', $datasets, $model->dataset ? $model->dataset->id : null,['class' => 'form-control m-input m-input--air']) }}
                        </div>
                        <div class="form-group m-form__group">
                            <span class="alert m-alert--default dataset-classes m--hide">Classes: <span class="dataset-classes-items"></span></span>
                        </div>
                        <div class="ui-sortable right-sortable">
                            @foreach($selected as $component)
                                @include('domain.ai_models.component', ['component' => $component])
                            @endforeach
                        </div>
                        <div class="m-portlet__foot m-portlet__foot--fit">
                            <div class="m-form__actions m-form__actions--modified">
                                <button type="submit" class="btn btn-accent">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
            </div>
        </div>
    </div>
@endsection
