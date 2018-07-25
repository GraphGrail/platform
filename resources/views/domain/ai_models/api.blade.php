<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */
?>


<div class="m-portlet m-portlet--tab">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
						<span class="m-portlet__head-icon m--hide">
						<i class="la la-gear"></i>
						</span>
                <h3 class="m-portlet__head-text">
                    Test trained model
                </h3>
            </div>
        </div>
    </div>
    <!--begin::Form-->
    <form class="m-form m-form--fit m-form--label-align-right" action="{{ route('ai-models.exec', ['model' => $model]) }}" method="POST">
        @csrf
        <div class="m-portlet__body">
            <div class="alert m-alert--default" role="alert">
                @if($result)
                    <div>Result: {{ $result->getData() }}</div>
                @endif
            </div>
            <div class="form-group m-form__group">
                <label for="exampleTextarea">Text</label>
                <textarea class="form-control m-input m-input--air" id="api_query" name="api_query" rows="3"></textarea>
            </div>
        </div>
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions">
                <button type="submit" class="btn btn-accent">Submit</button>
            </div>
        </div>
    </form>
    <!--end::Form-->
</div>
