<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\ComponentForm;
use App\Domain\Strategy\Component\Form\FieldForm;

class Settings extends ComponentForm
{
    protected $labels = [
        'epochs' => 'Number of epochs',
    ];

    public function getFieldsFormObjects(): array
    {
        $epochs = new FieldForm($this->createLabel('epochs'), $this->getEpochs());

        return [$epochs];
    }

    protected function getEpochs()
    {
        return \Form::number($this->createName('epochs'), $this->component->epochs ?? 25, ['class' => $this->class]);
    }
}
