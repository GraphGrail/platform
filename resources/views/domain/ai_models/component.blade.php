<div class="sortable-item {{ $component->optional ? '' : 'sortable-item--disabled' }} group">
    <div class="m-portlet__head sortable-item-head">
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
        <div class="sortable-item-head-arrow"></div>
    </div>
    <div class="sortable-item-body">
        @foreach($component->getFields() as $field)
            @if($field->getLabel())
                <div class="form-group m-form__group">
                    {{ $field->getLabel() }}
                    {{ $field->getInput() }}
                </div>
                <br>
            @else
                <div class="form-group m-form__group">
                    {{ $field->getInput() }}
                </div>
            @endif

        @endforeach
    </div>
</div>
