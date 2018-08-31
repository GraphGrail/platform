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
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    {{ __('Ai Models') }} <a href="{{ route('ai-models.create') }}" class="btn m-btn--pill m-btn--air btn-primary">+{{ __('Add') }}</a>
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
                                        <a href="javascript:void(0);" onclick="deleteModel({{ $model->id }})">
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
    <script language="javascript">
        function deleteModel(id) {
            if (!confirm("@lang('Are you sure?')")) {
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
@endsection
