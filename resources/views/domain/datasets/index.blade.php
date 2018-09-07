<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\Dataset\Dataset[] $datasets */
?>
@extends('layouts.app')

@section('content')
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h1 class="m-subheader__title">
                    {{ __('Datasets') }} <a href="{{ route('datasets.create') }}" class="btn m-btn--pill m-btn--air btn-primary">+{{ __('Add') }}</a>
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

        @if($system)
            @include('domain.datasets.list', ['datasets' => $system, 'title' => 'System datasets'])
        @endif

        @include('domain.datasets.list', ['datasets' => $datasets])
    </div>
@endsection

@section('scripts')
    @parent

    @if(\Illuminate\Support\Facades\Auth::user()->isNew)
        <script language="javascript">
            $(document).ready(function () {
                setTimeout(function () {
                    window.showEducationBlock(
                        "@lang('Hi, here you can choose demo dataset or upload your own.')",
                        "{{ url('/ai-models') }}"
                    );
                }, 300);
            });
        </script>
    @endif
@endsection
