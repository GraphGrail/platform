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
        @if($system)
            @include('domain.datasets.list', ['datasets' => $system, 'title' => 'System datasets'])
        @endif
        @include('domain.datasets.list', ['datasets' => $datasets])

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
<div class="modal fade" id="educationModal" tabindex="-1" role="dialog" aria-labelledby="modalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h4>@lang('Welcome. GraphGrailAi Platform allows you to create you own Ai application. For this you only need upload your text data and teach your neural network in a few clicks of mouse. After you are done, test your App pasting text example in form and pres Process. Also, you can integrate ready-made API in your business-process!')</h4>
            </div>
            <div class="modal-footer">
                <a href="{{ url('/datasets') }}"  id="modalNextButton" class="btn btn-primary">Next</a>
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
                }, 300);
            });
        </script>
    @endif
@endsection
