<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Exception;


class ExecutionException extends Exception
{
    public function __construct($errors = [])
    {
        parent::__construct(__('Strategy execution error'), $errors);
    }
}
