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

    private $id;

    /**
     * Create a new job instance.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @param Storage $storage
     * @return void
     * @throws \League\Csv\Exception
     */
    public function handle(Storage $storage): void
    {
        /** @var Dataset|null $dataset */
        $dataset = Dataset::query()->find($this->id);
        if (null === $dataset) {
            throw new \RuntimeException(sprintf('Not found dataset id: %s', $this->id));
        }

        if (!$storage->fileExists($dataset)) {
            \Log::warning('Dataset file not exist', ['dataset_id' => $dataset->id]);
            return;
        }
        $group = $dataset->labelGroup;
        $existLabels = $group->labels;

        $reader = Reader::createFromPath($storage->getPath($dataset));
        $reader->setDelimiter($dataset->delimiter);

        $records = $reader->getRecords();
        $first = true;
        foreach ($records as $offset => $record) {
            if ($first && $dataset->exclude_first_row) {
                $first = false;
                continue;
            }

            $text = $this->extractMessage($record);
            $category = $this->extractLabelTree($record);

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
            $dataset->data()->save($data);
        }
        $dataset->status = Dataset::STATUS_READY;
        $dataset->save();
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
