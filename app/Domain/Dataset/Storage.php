<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset;



class Storage extends \App\Domain\Storage
{
    protected $diskName = 'datasets';

    public function fileExists(Dataset $dataset): bool
    {
        return $this->getDisk()->exists($dataset->file);
    }

    public function getPath(Dataset $dataset)
    {
        return $this->getDisk()->getAdapter()->applyPathPrefix($dataset->file);
    }
}
