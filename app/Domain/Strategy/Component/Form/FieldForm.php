<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\Component\Form;


class FieldForm
{
    protected $label, $input;

    public function __construct($label, $input)
    {
        $this->label = $label;
        $this->input = $input;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getInput()
    {
        return $this->input;
    }
}
