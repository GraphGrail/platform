<?php

namespace App\Jobs;

use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Reader;
use League\Csv\Writer;

/**
 * @author Afanasyev Pavel <p.afanasev@graphgrail.com>
 */
class ConvertSeparatorOnDataset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;

    private $delimiter = ',';

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param Storage $storage
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

        if ($dataset->delimiter === $this->delimiter) {
            return;
        }

        $reader = Reader::createFromPath($storage->getPath($dataset));
        $reader->setDelimiter($dataset->delimiter);

        $file = new File($storage->getPath($dataset));
        $storageId = $file->hashName($dataset->user_id);
        $path = $storage->getDisk()->getAdapter()->applyPathPrefix($storageId);
        touch($path);

        $writer = Writer::createFromPath($path);
        $writer->setDelimiter($this->delimiter);
        $writer->insertAll($reader->getRecords());

        $oldStorageId = $dataset->file;
        $dataset->delimiter = $this->delimiter;
        $dataset->file = $storageId;
        if (!$dataset->save()) {
            throw new \RuntimeException('Can not update dataset id: $s', $dataset->id);
        }

        if (!$storage->getDisk()->delete($oldStorageId)) {
            \Log::warning('Can not delete old dataset file. StorageId: %s', $oldStorageId);
        }
    }
}