<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

?>
@extends('layouts.app')
@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    <h3>Dataset: {{ $dataset->name }}</h3>
                    <a href="{{ route('datasets.download', ['dataset' => $dataset]) }}"
                       class="btn m-btn--pill m-btn--air btn-primary">{{ __('Download') }}</a>
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
                            Labels
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
                            <tbody>
                            @foreach($dataset->labelGroup->labels as $label)
                                <tr>
                                    <td>{{ $label->text }}</th>
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


