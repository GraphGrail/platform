<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\ComponentForm;
use App\Domain\Strategy\Component\Form\FieldForm;

class StopWordsRemover extends ComponentForm
{
    protected $labels = [
        'language' => 'Стандартный набор стоп слов',
        'stopset' => 'Свой набо стоп-слов',
    ];

    protected $valueLabels = [
        'none' => 'Не использовать стандартный набор',
        'rus' => 'Rus',
        'en' => 'En',
    ];

    public function getFieldsFormObjects(): array
    {
        $language = new FieldForm($this->createLabel('language'), $this->getLanguage());
        $stopset = new FieldForm($this->createLabel('stopset'), $this->getStopSet());

        return [$language, $stopset];
    }

    protected function getLanguage()
    {
        return \Form::select($this->createName('language'), [
            'none' => $this->valueLabels['none'],
            'rus' => $this->valueLabels['rus'],
            'en' => $this->valueLabels['en'],

        ], $this->component->language ?? 'none', ['class' => $this->class]);
    }

    protected function getStopSet()
    {
        return \Form::textarea($this->createName('stopset'), $this->component->stopset ?? '', ['class' => $this->class]);
    }
}
