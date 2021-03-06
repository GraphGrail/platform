<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Exception;


class VerificationException extends Exception
{
    public function __construct($errors = [])
    {
        parent::__construct(__('Configuration invalid'), $errors);
    }
}
