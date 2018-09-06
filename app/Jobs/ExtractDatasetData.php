<?php

namespace App\Jobs;

use App\Domain\Dataset\Data;
use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\Label;
use App\Domain\Dataset\LabelGroup;
use App\Domain\Dataset\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use League\Csv\Reader;

class ExtractDatasetData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dataset;
    protected $excel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function handle()
    {
        $storage = new Storage();

        if (!$storage->fileExists($this->dataset)) {
            \Log::warning('Dataset file not exist', ['dataset_id' => $this->dataset->id]);
            return;
        }
        $group = $this->dataset->labelGroup;
        $existLabels = $group->labels;

        $reader = Reader::createFromPath($storage->getPath($this->dataset), 'r');
        $reader->setDelimiter(',');

        $records = $reader->getRecords();
        foreach ($records as $offset => $record) {
            $text = $this->extractMessage($record);
            $category = $this->extractLabelTree($record);
            \Log::info(sprintf('dataset data: %s:%s', $category, $text));

            $label = null;
            foreach ($existLabels as $existLabel) {
                if ($existLabel->text === $category) {
                    $label = $existLabel;
                    break;
                }
            }
            if (!$label) {
                $label = $this->createLabel($group, $category);
                $existLabels[] = $label;
            }
            $data = new Data(['text' => $text, 'label_id' => $label->id]);
            $this->dataset->data()->save($data);
        }
        $this->dataset->status = Dataset::STATUS_READY;
        $this->dataset->save();
    }

    private function createLabel(LabelGroup $group, string $text): Label
    {
        $label = new Label(['text' => $text]);
        $group->labels()->save($label);
        return $label;
    }

    protected function extractMessage($record)
    {
        return $record[1];
    }

    protected function extractLabelTree($record): string
    {
        return (string)$record[2];
    }
}
