<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Exception;


class Exception extends \Exception
{

    protected $errors = [];

    public function __construct(string $message, $errors = [])
    {
        parent::__construct($message);
        $this->errors = (array)$errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}
