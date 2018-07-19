<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\Dataset\LabelGroup[] $groups */
?>
<h4>Labels</h4>

@foreach($groups as $group)
    <h5>Set #{{ $group->id }}:</h5>
    <ul>
        @foreach($group->labels as $label)
            <li>{{$label->id}}: {{$label->text}}</li>
        @endforeach
    </ul>
    <hr>
@endforeach

