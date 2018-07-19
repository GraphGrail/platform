<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\Dataset\Dataset[] $datasets */
?>
<h4>Datasets</h4>
<ul>
@foreach($datasets as $dataset)
    <li><a href="{{ route('datasets.show', ['dataset'=> $dataset]) }}">{{$dataset->id}}: {{$dataset->name}}</a></li>
@endforeach
</ul>
