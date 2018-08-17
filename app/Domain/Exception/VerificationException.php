<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Exception;


class VerificationException extends Exception
{
    protected $errors = [];

    public function __construct($errors = [])
    {
        parent::__construct(__('Configuration invalid'));
        $this->errors = (array)$errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }


}
