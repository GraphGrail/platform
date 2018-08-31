<?php

namespace App\Console\Commands\Domain;

use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\LabelGroup;
use App\Domain\Dataset\Storage;
use App\Jobs\ExtractDatasetData;
use Illuminate\Console\Command;

class CreateSystemDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataset:create:system {path} {lang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload system dataset, params - :path, :lang - en|ru';

    private $storage;

    public function __construct()
    {
        parent::__construct();

        $this->storage = new Storage();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $lang = $this->argument('lang');
        $inputPath = $this->argument('path');
        $dir = \dirname(__DIR__, 4);
        $source = $dir . DIRECTORY_SEPARATOR . $inputPath;
        if (!file_exists($source)) {
            $this->error("File doesn't exist: " . $source);
            return;
        }

        $destination = basename($inputPath);

        $source = new \Illuminate\Http\File($source);
        $path = $this->storage->getDisk()->putFileAs(
            0, $source, $destination
        );
        $dataset = new Dataset([
            'file' => $path,
            'name' => 'System dataset',
            'user_id' => 0,
            'status' => Dataset::STATUS_NEW,
            'lang' => $lang,
        ]);
        $group = new LabelGroup(['user_id' => 0]);
        $group->save();

        $dataset->labelGroup()->associate($group);
        $dataset->save();

        ExtractDatasetData::dispatch($dataset)->onQueue('dataset');
    }
}
