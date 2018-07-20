<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\Component\Form;


use App\Domain\Component;

abstract class ComponentForm
{
    protected $component;

    protected $labels = [];

    public function __construct(Component $component)
    {
        $this->component = $component;
    }

    protected function getPrefix(): string
    {
        return \get_class($this->component);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function createLabel($name)
    {
        return \Form::label($this->createName($name), $this->labels[$name]);
    }

    protected function createName($name)
    {
        return $this->getPrefix().'['.$name.']';
    }
}
