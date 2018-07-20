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

<form method="{{ $method }}" action="{{ route('ai-models.store') }}">
    @csrf
    @foreach($provider->all() as $strategy)
        <label class="">{{ $strategy->name() }}</label>
        @foreach($strategy->getComponents() as $component)
            <div>
                <p>{{ $component->description() }}</p>
                @foreach($component->getFields() as $field)
                    {{ $field->getLabel() }}
                    {{ $field->getInput() }}
                    <br>
                @endforeach
            </div>
        @endforeach
    @endforeach
    <button type="submit">Save</button>
</form>


