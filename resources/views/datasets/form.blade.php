<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

/** @var \App\Domain\Dataset\Dataset $dataset */
//$url = $dataset->id ? url('datasets') : url('datasets');
$method = $dataset->id ? 'put' : 'post';
?>
<h1>Upload dataset .csv-file</h1>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if($dataset->file)
    <h4>{{$dataset->file}}</h4>
@endif


{!! Form::model($dataset, ['url' => url('datasets'), 'files' => true, 'method' => $method]); !!}
{!! Form::label('file', 'File'); !!}
{!! Form::file('dataset'); !!}
{!! Form::submit('Save') !!}
{!! Form::close() !!}


