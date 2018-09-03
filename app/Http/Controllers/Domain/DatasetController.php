<?php

namespace App\Http\Controllers\Domain;

use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\LabelGroup;
use App\Domain\Dataset\Storage;
use App\Domain\Dataset\Validator\DelimiterRule;
use App\Http\Controllers\Controller;
use App\Jobs\ExtractDatasetData;
use http\Exception\RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
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
        $system = Dataset::query()->where(['user_id' => 0])->get()->all();

        return view('domain/datasets/index', ['datasets' => $datasets, 'system' => $system]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('domain/datasets/form', ['dataset' => new Dataset(['user_id' => Auth::id()])]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $request->validate([
            'dataset' => 'required|file|mimes:csv,txt',
            'name' => 'required|string|max:255',
            'lang' => 'required|string|max:2',
        ]);
        if (!$path = $request->file('dataset')->store(
            $userId, $this->storage->getDiskName()
        )) {
            throw new RuntimeException('File not saved');
        }
        $dataset = new Dataset([
            'file' => $path,
            'name' => $request->get('name'),
            'lang' => $request->get('lang'),
            'user_id' => $userId,
            'status' => Dataset::STATUS_NEW,
        ]);

        try {
            $this->validateDataset($dataset);
        } catch (ValidationException $e) {
            $storage = new Storage();
            $storage->delete($dataset);

            throw $e;
        }

        $group = new LabelGroup(['user_id' => $userId]);
        $group->save();

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
        return view('domain/datasets/show', ['dataset' => $dataset]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domain\Dataset\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function edit(Dataset $dataset)
    {
        return view('domain/datasets/form', ['dataset' => $dataset]);
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
            'name' => 'required|string|max:255',
            'lang' => 'required|string|max:2',
        ]);

        $dataset->name = $request->get('name');
        $dataset->lang = $request->get('lang');
        $dataset->save();

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

    private function validateDataset(Dataset $dataset)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['dataset' => $dataset], ['dataset' => new DelimiterRule()]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
