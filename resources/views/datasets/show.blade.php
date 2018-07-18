<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

?>

<h3>{{ $dataset->name }}</h3>
<a href="<?=route('datasets.download', ['dataset' => $dataset])?>">Download</a>
