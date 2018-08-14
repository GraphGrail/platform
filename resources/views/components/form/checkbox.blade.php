<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */
$id = str_replace('\\', '_', $name.$value);
?>
<div class="form-group form-check">
    {{ Form::checkbox($name, $value, $checked, array_merge(['class' => 'form-check-input', 'id' => $id], $attributes)) }}
    {{ Form::label($id, $label, ['class' => 'form-check-label', 'for' => $id]) }}
</div>
