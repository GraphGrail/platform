<?php

namespace App\Console\Commands;

use App\Domain\Dataset\Dataset;
use Illuminate\Console\Command;

class SetFieldsForDatasets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataset:set {--delimiter=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set delimiter for existing datasets';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $delimiter = $this->option('delimiter');

        if (empty($delimiter)) {
            return;
        }

        Dataset::query()->chunk(10, function ($datasets) use ($delimiter) {
            /** @var Dataset $dataset */
            foreach ($datasets as $dataset) {
                $dataset->delimiter = $delimiter;
                $dataset->save();
                $this->getOutput()->writeln(sprintf('Change delimiter for dataset id: %s', $dataset->id));
            }
        });
    }
}
