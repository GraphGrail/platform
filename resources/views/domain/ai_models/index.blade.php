<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\AiModel[] $models */
?>
<h4>Ai models</h4>
<a href="{{ route('ai-models.create') }}">Create</a>
<ul>
@foreach($models as $model)
    <li><a href="{{ route('ai-models.show', ['model'=> $model]) }}">{{$model->id}}: {{$model->name}}</a></li>
@endforeach
</ul>
