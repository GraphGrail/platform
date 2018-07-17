<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain;


abstract class Storage
{
    protected $disk;
    protected $diskName = 'local';

    public function __construct()
    {
        $this->disk = \Illuminate\Support\Facades\Storage::disk($this->diskName);
    }

    function put($path, $contents) {
        $this->disk->put($path, $contents);
        return $this;
    }

    function get($path) {
        if (!$this->disk->exists($path)) {
            return null;
        }
        return $this->disk->get($path);
    }
}
