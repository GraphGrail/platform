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
    return [$d->id => $d->getFullName()];
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
    @parent

    <link href="{{ asset('css/ui.css') }}" rel="stylesheet">
@endsection
@section('scripts')
    @parent

    <script src="{{ asset('js/ui.js') }}" defer></script>
@endsection

@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    <h3>@lang('Ai model') {{ $model->getFullName() }}</h3>
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
                            @lang('Create model') "{{ $strategy->name() }}"
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
                            {{ \Form::label('dataset', __('Dataset')) }}
                            {{ \Form::select('dataset', $datasets, $model->dataset ? $model->dataset->id : null,['class' => 'form-control m-input m-input--air']) }}
                        </div>
                        <div class="form-group m-form__group">
                            <span class="alert m-alert--default dataset-classes m--hide">@lang('Classes'): <span class="dataset-classes-items"></span></span>
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

    <div class="modal fade" id="educationModal" tabindex="-1" role="dialog" aria-labelledby="modalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <h4>@lang('This section for Ai neural network training. You can use drag-n-drop interface to choose Ai Components from left palette and then adjust their settings in right palette.')</h4>
                </div>
                <div class="modal-footer">
                    <div id="select_first" class="educationBlock-button">
                        <h5>Toxic comments dataset</h5>
                        <p>Dataset with large number of Wikipedia comments which have been labeled by human raters for toxic behavior</p>
                    </div>
                    <div id="select_second" class="educationBlock-button">
                        <h5>IMDB dataset</h5>
                        <p>IMDB movie reviews dataset for Sentiment Analysis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    @parent

    @if(\Illuminate\Support\Facades\Auth::user()->isNew)
        <script language="javascript">
            $(document).ready(function () {
                setTimeout(function () {
                    window.showEducationBlock();
                }, 500);
            });
        </script>
    @endif
@endsection
