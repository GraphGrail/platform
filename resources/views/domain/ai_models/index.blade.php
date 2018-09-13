<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\AiModel[] $models */
?>
@extends('layouts.app')

@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto relative">
                <h1 class="m-subheader__title">
                    {{ __('Ai Models') }} <a href="{{ route('ai-models.create') }}" class="btn m-btn--pill m-btn--air btn-primary">+{{ __('Add') }}</a>
                </h1>
                <div class="modal fade absolute-modal" id="educationModal" tabindex="-1" role="dialog" aria-labelledby="modalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content modal-triangle-left">
                                <div class="modal-body">
                                    <h4>@lang('Here you can choose Ai models you trained previously. If you have no them - let\'s create new one!')</h4>
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ url('/ai-models/create') }}" id="modalNextButton" class="btn btn-primary">Next</a>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                <th>@lang('Name')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Actions')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($models as $model)
                                <tr>
                                    <th scope="row">{{ $model->id }}</th>
                                    <td><a href="{{ route('ai-models.show', ['model' => $model]) }}">{{$model->getFullName()}}</a></td>
                                    <td>{{ __($model->statusLabel()) }}</td>
                                    <td>
                                        <a href="javascript:void(0);" onclick="deleteModel({{ $model->id }}, '{{ $model->getFullName() }}')">
                                            <i class="fa fa-trash" title="@lang('Delete')"></i>
                                        </a>
                                    </td>
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
@section('scripts')
    @parent

    <script language="javascript">
        function deleteModel(id, name) {
            if (!confirm("@lang('Are you sure?')" + " " + "@lang('Delete')" + " " + name + "?")) {
                return;
            }
            const url = "{{ url('ai-models') }}/" + id;
            axios.delete(url)
                .then(function (response) {
                    window.location.reload();
                })
                .catch(function (error) {
                    console.log(error);
                    window.location.reload();
                });
        }
    </script>

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
