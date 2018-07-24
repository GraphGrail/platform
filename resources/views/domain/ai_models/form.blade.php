<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\AiModel $model */
$method = $model->id ? 'PUT' : 'POST';

$route = $model->id ? route('ai-models.update', ['model' => $model]) : route('ai-models.store');

/** @var \App\Domain\Strategy\StrategyProvider $provider */
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

        @foreach($provider->all() as $strategy)
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
                            {{ \Form::select('dataset', $datasets, null,['class' => 'form-control m-input m-input--air']) }}
                        </div>
                        @foreach($strategy->getComponents() as $component)
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
                            <button type="submit" class="btn btn-accent">Submit</button>
                        </div>
                    </div>
                </form>
                <!--end::Form-->
            </div>
        @endforeach
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            var updateFields = function() {
                $('div.component-field-repeatable').each(function (index, element) {
                    console.log(index);
                });
            };



            $('div.component-field-repeatable .add-repeatable').on('click', function () {
                var fields = $(this).parents('.component-field-repeatable');
                fields.parent().append(fields.clone(true));
                $(this).remove();
                updateFields();
            });
        });
    </script>
@endsection
