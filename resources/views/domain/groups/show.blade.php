<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

?>

<h3>Set #{{ $group->id }}:</h3>
<ul>
    @foreach($group->labels as $label)
        <li>{{$label->id}}: {{$label->text}}</li>
    @endforeach
</ul>
<hr>
