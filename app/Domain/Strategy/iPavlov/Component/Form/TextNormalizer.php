<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\ComponentForm;
use App\Domain\Strategy\Component\Form\FieldForm;

class TextNormalizer extends ComponentForm
{
    protected $labels = [
        'norm_method' => 'Choose normalizer method: lemmatization or stemming (depends mostly on language, read more at https://nlp.stanford.edu/IR-book/html/htmledition/stemming-and-lemmatization-1.htm',
        'tokenizer' => 'Tokenizer algorithm',
    ];

    protected $valueLabels = [
        'none' => 'None',
        'lemmatize' => 'Lemmatization',
        'stem' => 'Stemming',
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
        return \Form::select($this->createName('norm_method'), [
            'lemmatize' => __($this->valueLabels['lemmatize']),
            'stem' => __($this->valueLabels['stem']),
            'none' => __($this->valueLabels['none']),

        ], $this->component->norm_method ?? 'lemmatize', ['class' => $this->class]);
    }

    protected function getTokenizer()
    {
        return \Form::select($this->createName('tokenizer'), [
            'treebank' => __($this->valueLabels['treebank']),
            'word_tokenize' => __($this->valueLabels['word_tokenize']),

        ], $this->component->tokenizer ?? 'treebank', ['class' => $this->class]);
    }
}
