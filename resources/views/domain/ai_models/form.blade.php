<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\AiModel $model */
$method = $model->id ? 'put' : 'post';

/** @var \App\Domain\Strategy\StrategyProvider $provider */
?>
<h1>Ai model form</h1>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<h4>{{$model->id}}</h4>

@foreach($provider->all() as $strategy)
    {{ $strategy->name() }}
@endforeach

{!! Form::model($model, ['url' => url('ai-models'), 'method' => $method]); !!}

{!! Form::submit('Save') !!}
{!! Form::close() !!}


