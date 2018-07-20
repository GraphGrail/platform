<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\FieldForm;

class TextNormalizer
{
    protected $labels = [
        'norm_method' => 'Способ приведения слов к нормальной форме: лемматизация - приведение к инфинитиву, стемминг - отбрасывание окончаний',
        'tokenizer' => 'Алгоритм разбиения текста на токены',
    ];

    protected $valueLabels = [
        'none' => 'Нет',
        'lemmatize' => 'Лемматизация',
        'stem' => 'Стемминг',
        'treebank' => 'Treebank',
        'word_tokenize' => 'WordTokenize',
    ];

    public function getFieldsFormObjects(): array
    {
        $normMethod = new FieldForm($this->createLabel('norm_method'), $this->getNormMethod());
        $tokenizer = new FieldForm($this->createLabel('tokenizer'), $this->getTokenizer());

        return [$normMethod, $tokenizer];
    }

    protected function getNormMethod()
    {
        return \Form::select('norm_method', [
            'lemmatize' => $this->valueLabels['lemmatize'],
            'stem' => $this->valueLabels['stem'],
            'none' => $this->valueLabels['none'],

        ], 'lemmatize');
    }

    protected function getTokenizer()
    {
        return \Form::select('tokenizer', [
            'treebank' => $this->valueLabels['treebank'],
            'word_tokenize' => $this->valueLabels['word_tokenize'],

        ], 'treebank');
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function createLabel($name)
    {
        return \Form::label($name, $this->labels[$name]);
    }
}
