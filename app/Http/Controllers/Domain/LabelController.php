<?php

namespace App\Http\Controllers\Domain;

use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\LabelGroup;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LabelController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = LabelGroup::query()->where(['user_id' => Auth::id()])->get()->all();

        return view('domain/groups/index', ['groups' => $groups]);
    }

    /**
     * Display the specified resource.
     *
     * @param LabelGroup $group
     * @return \Illuminate\Http\Response
     */
    public function show(LabelGroup $group)
    {
        return view('domain/groups/show', ['group' => $group]);
    }

    public function json(Dataset $dataset)
    {
        return $dataset->labelGroup->labels;
    }
}
