<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */
/** @var \App\Domain\AiModel $model */

$train = url('ai-models/train', ['model' => $model]);
$edit = route('ai-models.edit', ['model' => $model]);
$url = null;

$message = '';
$method = 'POST';
if ($model->status === \App\Domain\AiModel::STATUS_VERIFYING_CONFIG) {
    $message = __('Configuration testing');
}

if ($model->status === \App\Domain\AiModel::STATUS_VERIFY_CONFIG_FAIL) {
    $url = $edit;
    $method = 'GET';
    $message = __('Configuration test failed. Change configuration');
}

if ($model->status === \App\Domain\AiModel::STATUS_VERIFY_CONFIG_OK) {
    $url = $train;
    $message = __('Configuration test passed. Now you may train your model');

    if (!$model->dataset) {
        $method = 'GET';
        $url = $edit;
        $message = __('Configuration test passed. But you need to select dataset');
    }
}

if ($model->status === \App\Domain\AiModel::STATUS_TRAINING) {
    $message = __('Model training. ');
    if ($model->performance) {
        $message .= sprintf('Quality %s%%', $model->performance);
    }
}

?>
@extends('layouts.app')
@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    Ai Model: {{ $model->id }}{{ $model->dataset ? '-' . $model->dataset->name : '' }}. {{ $model->statusLabel() }}.
                    @if($model->performance)
                        Quality {{ $model->performance }}%
                    @endif
                </h1>
            </div>
            @if ($model->dataset)
                <a href="{{ route('datasets.download', ['dataset' => $model->dataset]) }}"
                   class="btn m-btn--pill m-btn--air btn-primary">{{ __('Download') }}</a>
                <a class="btn m-btn--pill m-btn--air btn-warning m-btn--wide" href="{{ $edit }}">Edit</a>
            @else
                <a class="btn m-btn--pill m-btn--air btn-warning m-btn--wide" href="{{ $edit }}">Empty dataset</a>
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
        <div class="form-group m-form__group m--margin-top-10">
            @if ($model->getErrors())
                @foreach ($model->getErrors() as $error)
                    <div class="alert alert-danger m-alert m-alert--default" role="alert">
                        {{ $error }}
                    </div>
                @endforeach
            @endif
        </div>
        @if($model->status !== \App\Domain\AiModel::STATUS_READY)
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
                            <p>{{ $message }}</p>
                            @if($url)
                                <form method="POST" action="{{ $url }}">
                                    @method($method)
                                    @csrf
                                    <input type="hidden" name="model" value="{{ $model->id }}">
                                    <input type="hidden" name="dataset" value="{{ $model->dataset ? $model->dataset->id : '' }}">
                                    <button class="btn btn-accent " type="submit">Start</button>
                                </form>
                            @endif
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
                            <strong>Post example:</strong>
                            <div class="alert m-alert--default" role="alert">
                                <div>
                                    <div><strong>POST</strong> {{ route('api.exec', ['model' => $model]) }}</div>
                                    <div><strong>Accept:</strong> application/json</div>
                                    <div><strong>Content-Type:</strong> application/json</div>
                                    <div><strong>Authorization:</strong> Bearer {{ Auth::user()->api_token }}</div>
                                    <div><strong>Body:</strong></div>
                                    <div>{"data": "Text to classify"}</div>
                                </div>
                            </div>
                            <strong>Curl example:</strong>
                            <div class="alert m-alert--default" role="alert">
                                curl {{ route('api.exec', ['model' => $model]) }} -X POST -H 'Authorization: Bearer {{ Auth::user()->api_token }}' -H 'Content-Type: application/json' --data '{"data":"Text to classify"}'
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->

                    <div class="m-separator m-separator--dashed"></div>
                </div>
            </div>
            @include('domain.ai_models.api')
        @endif
    </div>
@endsection

@if($model->status == \App\Domain\AiModel::STATUS_TRAINING)
    @section('scripts')
        <script language="javascript">
            let checker = function() {
                axios.get("{{ route('ai-models.status', ['model' => $model]) }}")
                    .then(function (response) {
                        if (!response.data) {
                            return;
                        }
                        if (response.data == "{{ \App\Domain\AiModel::STATUS_TRAINING }}") {
                            return;
                        }
                        window.location.reload();
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            };
            setInterval(checker, 5000);
        </script>
    @endsection
@endif
