<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


class Result
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
