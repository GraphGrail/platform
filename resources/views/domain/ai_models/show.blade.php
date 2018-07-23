<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */
/** @var \App\Domain\AiModel $model */
?>

<h3>Ai model {{ $model->id }}</h3>
<label>{{ $model->statusLabel() }}</label>

@if($model->status === \App\Domain\AiModel::STATUS_NEW)
    <div>
    <form method="POST" action="{{ url('ai-models/train', ['model' => $model]) }}">
        @csrf
        <input type="hidden" name="model" value="{{ $model->id }}">
        <input type="hidden" name="dataset" value="{{ $model->dataset->id }}">
        <button type="submit">Start learning</button>
    </form>
    </div>
@endif
