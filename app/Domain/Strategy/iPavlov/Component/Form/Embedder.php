<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\ComponentForm;
use App\Domain\Strategy\Component\Form\FieldForm;

class Embedder extends ComponentForm
{
    protected $labels = [
        'emb_type' => 'Способ преобразования текста в признаковое пространство',
    ];

    protected $valueLabels = [
        'pretrained_compressed' => 'Сжатая модель (предобученная модель, ft или w2v)',
        'pretrained_ft' => 'FastText (предобученная модель)',
        'pretrained_w2v' => 'Word2Vec (предобученная модель)',
        'acquired_w2v' => 'Word2Vec (модель, обучаемая на имеющихся данных)',
        'acquired_ft' => 'FastText (модель, обучаемая на имеющихся данных)',
        'bow' => 'Мешок слов',
        'num_sequence' => 'Последовательность целых чисел (номеров в словаре)',
    ];

    public function getFieldsFormObjects(): array
    {
        $type = new FieldForm($this->createLabel('emb_type'), $this->getType(), ['class' => $this->class]);

        return [$type];
    }

    protected function getType()
    {
        return \Form::select($this->createName('emb_type'), $this->valueLabels, $this->component->emb_type ?? 'pretrained_compressed', ['class' => $this->class]);
    }

}
