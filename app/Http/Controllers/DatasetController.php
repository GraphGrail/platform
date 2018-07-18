<?php

namespace App\Http\Controllers;

use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\LabelGroup;
use App\Domain\Dataset\Storage;
use App\Jobs\ExtractDatasetData;
use App\Jobs\ExtractDatasetLabelTree;
use http\Exception\RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DatasetController extends Controller
{
    protected $storage;

    public function __construct()
    {
        $this->storage = new Storage();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datasets = Dataset::query()->where(['user_id' => Auth::id()])->get()->all();

        return view('datasets/index', ['datasets' => $datasets]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('datasets/form', ['dataset' => new Dataset(['user_id' => Auth::id()])]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $request->validate([
            'dataset' => 'required|file|mimes:csv,txt',
        ]);
        if (!$path = $request->file('dataset')->store(
            $userId, $this->storage->getDiskName()
        )) {
            throw new RuntimeException('File not saved');
        }
        $group = new LabelGroup(['user_id' => $userId]);
        $group->save();

        $dataset = new Dataset([
            'file' => $path,
            'name' => $request->file('dataset')->getClientOriginalName(),
            'user_id' => $userId,
        ]);
        $dataset->labelGroup()->associate($group);
        $dataset->save();

        ExtractDatasetData::dispatch($dataset)->onQueue('dataset');

        return Redirect::to('datasets');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Domain\Dataset\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function show(Dataset $dataset)
    {
        return view('datasets/show', ['dataset' => $dataset]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domain\Dataset\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function edit(Dataset $dataset)
    {
        return view('datasets/form', ['dataset' => $dataset]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Domain\Dataset\Dataset $dataset
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Dataset $dataset)
    {
        $userId = $request->user()->id;
        if ($dataset->user_id != $userId) {
            throw new AccessDeniedHttpException('Dataset belong to another user');
        }
        $request->validate([
            'dataset' => 'required|file|mimes:csv',
        ]);
        if (!$path = $request->file('dataset')->store(
            $userId, $this->storage->getDiskName()
        )) {
            throw new RuntimeException('File not saved');
        }

        $dataset->file = $path;
        $dataset->name = $request->file('dataset')->getClientOriginalName();
        $dataset->save();

        ExtractDatasetData::dispatch($dataset)->onQueue('dataset');
        return Redirect::to('/datasets/' . $dataset->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domain\Dataset\Dataset $dataset
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Dataset $dataset)
    {
        $dataset->delete();
        return Redirect::to('datasets');
    }

    public function download(Dataset $dataset)
    {
        if ($dataset->user_id !== Auth::id()) {
            throw new RuntimeException('Dataset not found');
        }
        return $this->storage->getDisk()->download($dataset->file, $dataset->name);
    }
}
