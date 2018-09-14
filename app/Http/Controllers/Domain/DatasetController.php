<?php

namespace App\Http\Controllers\Domain;

use App\Domain\Dataset\Data;
use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\LabelGroup;
use App\Domain\Dataset\Storage;
use App\Domain\Dataset\Validator\DelimiterRule;
use App\Http\Controllers\Controller;
use App\Jobs\ExtractDatasetData;
use http\Exception\RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Jobs\ConvertSeparatorOnDataset;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     * @return View
     */
    public function index(): View
    {
        $datasets = Dataset::query()->where(['user_id' => Auth::id()])->get()->all();
        $system = Dataset::query()->where(['user_id' => 0])->get()->all();

        return view('domain/datasets/index', ['datasets' => $datasets, 'system' => $system]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('domain/datasets/create', ['dataset' => new Dataset(['user_id' => Auth::id()])]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = \Validator::make(
            $request->all(),
            [
                'dataset' => 'required|file|mimes:csv,txt',
                'name' => 'required|string|max:255',
                'lang' => 'required|string|max:2',
                'exclude_first_row' => 'boolean',
                'delimiter' => 'required|string|max:1',
            ]
        )->validate();

        $userId = $request->user()->id;
        $file = $request->file('dataset');
        if (!$path = $file->store($userId, $this->storage->getDiskName())) {
            throw new RuntimeException('File not saved');
        }

        $dataset = new Dataset(
            [
                'file' => $path,
                'name' => $data['name'],
                'lang' => $data['lang'],
                'user_id' => $userId,
                'status' => Dataset::STATUS_NEW,
                'exclude_first_row' => (bool)$data['exclude_first_row'],
                'delimiter' => $data['delimiter'],
            ]
        );

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

        ConvertSeparatorOnDataset::dispatch($dataset->id)->chain(
            [
                new ExtractDatasetData($dataset->id),
            ]
        )->onQueue('dataset');

        return Redirect::to('datasets');
    }

    /**
     * Display the specified resource.
     *
     * @param Dataset $dataset
     * @return View
     */
    public function show(Dataset $dataset): View
    {
        return view('domain/datasets/show', ['dataset' => $dataset]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Dataset $dataset
     * @return View
     */
    public function edit(Dataset $dataset): View
    {
        return view('domain/datasets/edit', ['dataset' => $dataset]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  Dataset $dataset
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, Dataset $dataset): RedirectResponse
    {
        $userId = $request->user()->id;
        if ($dataset->user_id !== $userId) {
            throw new AccessDeniedHttpException('Dataset belong to another user');
        }
        $data = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'lang' => 'required|string|max:2',
            ]
        )->validate();

        $dataset->name = $data['name'];
        $dataset->lang = $data['lang'];
        $dataset->save();

        return Redirect::to('/datasets/' . $dataset->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Dataset $dataset
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Dataset $dataset): \Illuminate\Http\Response
    {
        if ($dataset->user_id !== Auth::id()) {
            return Response::make();
        }

        $this->storage->getDisk()->delete($dataset->file);
        $dataset->delete();
        Data::query()->where(['dataset_id' => $dataset->id])->delete();

        return Response::make();
    }

    public function download(Dataset $dataset): StreamedResponse
    {
        if ($dataset->user_id && $dataset->user_id !== Auth::id()) {
            throw new RuntimeException('Dataset not found');
        }
        return \Storage::disk()->download($dataset->file, $dataset->name);
    }

    /**
     * @param Dataset $dataset
     * @throws ValidationException
     */
    private function validateDataset(Dataset $dataset): void
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['dataset' => $dataset], ['dataset' => new DelimiterRule($dataset->delimiter)]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
