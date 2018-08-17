<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\Component\Form;


use App\Domain\Component;

abstract class ComponentForm
{
    protected $component;
    protected $class = 'form-control m-input m-input--air';

    protected $labels = [];

    public function __construct(Component $component)
    {
        $this->component = $component;
    }

    protected function getStrategyPrefix(): string
    {
        return \get_class($this->component->getStrategy());
    }

    protected function getComponentPrefix(): string
    {
        return $this->component::name();
    }

    public function getPrefix()
    {
        return $this->getStrategyPrefix().'['.$this->getComponentPrefix().']';
    }

    protected function createName($name)
    {
        return $this->getPrefix().'['.$name.']';
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function createLabel($name)
    {
        return \Form::label($this->createName($name), $this->labels[$name]);
    }
}
