<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\AiModel $model */

$configuration = $model->configuration;
$method = $model->id ? 'PUT' : 'POST';

$route = $model->id ? route('ai-models.update', ['model' => $model]) : route('ai-models.store');

/** @var \App\Domain\Strategy\Strategy $strategy */
/** @var \App\Domain\Dataset\Dataset[] $datasets */

$datasets = collect($datasets)->mapWithKeys(function (\App\Domain\Dataset\Dataset $d) {
    return [$d->id => sprintf('%s: %s', $d->id, $d->name)];
})->prepend('None', 0)->all();
?>
@extends('layouts.app')

@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    <h3>Ai model</h3>
                </h1>
            </div>
        </div>
    </div>
    <!-- END: Subheader -->


    <div class="m-content">
        <div class="form-group m-form__group m--margin-top-10">
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert m-alert m-alert--default" role="alert">
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
            <!--begin::Form-->
            <form class="col-md-8 m-form m-form--fit m-form--label-align-right" method="POST" action="{{ $route }}">
                @method($method)
                @csrf
                <input type="hidden" name="strategy" value="{{ $strategy->getFormName() }}">
                <div class="m-portlet__body">
                    <div class="form-group m-form__group">
                        {{ \Form::label('dataset', 'Dataset') }}
                        {{ \Form::select('dataset', $datasets, $model->dataset ? $model->dataset->id : null,['class' => 'form-control m-input m-input--air']) }}
                    </div>
                    @foreach($strategy->getComponents($model->configuration) as $component)
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <span class="m-portlet__head-icon m--hide">
                                        <i class="la la-gear"></i>
                                    </span>
                                    <h4 class="m-portlet__head-text">
                                        {{ $component->description() }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        @foreach($component->getFields() as $field)
                            <div class="form-group m-form__group">
                                {{ $field->getLabel() }}
                                {{ $field->getInput() }}
                            </div>
                            <br>
                        @endforeach
                    @endforeach
                </div>

                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions">
                        <button type="submit" class="btn btn-accent">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
            <!--end::Form-->
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('div.component-field-repeatable .add-repeatable').on('click', function () {
                let fields = $(this).parents('.component-field-repeatable');
                let clone = fields.clone(true);
                clone.find('.remove-repeatable').removeClass('m--hide');

                fields.parent().append(clone);
                $(this).addClass('m--hide');
                fields.find('.remove-repeatable').removeClass('m--hide');
            });
            $('div.component-field-repeatable .remove-repeatable').on('click', function () {
                let fields = $(this).parents('.component-field-repeatable');
                let parent = fields.parent();
                fields.remove();

                let last = parent.find('.component-field-repeatable').last();

                parent.find('.add-repeatable').addClass('m--hide');
                last.find('.add-repeatable').removeClass('m--hide');

                fields = parent.find('.component-field-repeatable');
                fields.find('.remove-repeatable').removeClass('m--hide');
                if (fields.length === 1) {
                    parent.find('.remove-repeatable').addClass('m--hide')
                }
            });
        });
    </script>
@endsection
