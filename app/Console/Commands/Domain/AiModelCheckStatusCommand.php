<?php

namespace App\Console\Commands\Domain;

use App\Domain\AiModel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AiModelCheckStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status for learning models';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        AiModel::query()->where(['status' => AiModel::STATUS_LEARNING])->chunk(200, function (Collection $models) {
            /** @var AiModel $model */
            foreach ($models as $model) {
                if (!$strategy = $model->configuration->strategy()) {
                    continue;
                }
                $strategy->status($model);
            }
        });
    }
}
