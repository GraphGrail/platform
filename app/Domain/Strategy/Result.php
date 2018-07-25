<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


class Result
{
    protected $query, $data;

    public function __construct($query = null, $data = null)
    {
        $this->query = $query;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getQuery()
    {
        return $this->query;
    }
}
