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
        'language' => 'Default language-specific',
        'stopset' => 'Add your own',
    ];

    protected $valueLabels = [
        'none' => 'I fill my stopwords (do not use default list)',
        'rus' => 'Rus',
        'en' => 'En',
    ];

    private $sets = [
        'rus' => ['на', 'ж', 'или', 'ничего', 'куда', 'чего', 'ним', 'были', 'том', 'не', 'он', 'три', 'можно', 'моя', 'если', 'здесь', 'была', 'что', 'все', 'всю', 'раз', 'будто', 'для', 'много', 'этой', 'то', 'наконец', 'у', 'чуть', 'она', 'там', 'еще', 'ли', 'хоть', 'вас', 'может', 'так', 'этого', 'тут', 'разве', 'вы', 'после', 'чем', 'как', 'ему', 'них', 'вот', 'ней', 'во', 'больше', 'к', 'всегда', 'этот', 'него', 'нибудь', 'два', 'тогда', 'над', 'ведь', 'всего', 'сам', 'надо', 'нет', 'такой', 'бы', 'потому', 'почти', 'про', 'нас', 'его', 'когда', 'им', 'другой', 'меня', 'сейчас', 'того', 'об', 'тот', 'тоже', 'в', 'тем', 'от', 'их', 'чтоб', 'какой', 'я', 'себе', 'ну', 'ее', 'вдруг', 'будет', 'эти', 'через', 'же', 'лучше', 'эту', 'есть', 'совсем', 'свою', 'за', 'да', 'зачем', 'из', 'между', 'нельзя', 'перед', 'всех', 'быть', 'до', 'более', 'со', 'хорошо', 'и', 'нее', 'но', 'мой', 'под', 'никогда', 'только', 'они', 'этом', 'иногда', 'конечно', 'мы', 'теперь', 'ей', 'какая', 'мне', 'вам', 'тебя', 'о', 'один', 'по', 'с', 'ты', 'а', 'где', 'без', 'уж', 'потом', 'чтобы', 'себя', 'было', 'кто', 'был', 'при', 'опять', 'впрочем', 'ни', 'даже', 'уже'],
        'en' => ['and', 'hasn', 'myself', 'before', 'hadn', 'is', 'few', 'because', "hadn't", 'itself', 'whom', 'they', 'don', 'who', 'didn', 'd', "won't", 'weren', 'each', 'to', 'its', 'off', 'm', 'there', 'more', 'hers', 'mustn', 'having', 'here', "hasn't", 'these', "should've", 'are', 'needn', 'her', 'been', "wouldn't", 'herself', 'a', 'until', "aren't", 'doesn', "doesn't", 'she', 'shan', 'haven', 'some', 'only', 'now', "shan't", 'at', 'you', 'nor', 'under', 'below', 'couldn', 'wasn', 'will', 'our', 'can', "wasn't", 'above', 'through', 'no', "don't", 'had', "mightn't", 'y', 'such', 'or', 'mightn', "needn't", 'should', 'of', 'any', 's', 'this', 'those', 'do', "haven't", 'his', 'doing', 'why', 'my', 'be', 'theirs', 'o', "didn't", 'than', 'has', 'what', 'ourselves', 'but', 'where', 'aren', 'most', 'for', 'being', "couldn't", 'have', 'how', 'about', 'on', 'he', 'against', 'shouldn', 'too', 'yours', 'not', 'themselves', 'very', "you'd", 'did', 'the', 'does', 'if', 'in', 'which', 'ain', "you'll", 'once', 'we', 'over', 'them', 'same', 'then', "it's", 'again', "shouldn't", 'himself', 'while', 'both', 'won', "you've", 'yourselves', 'ma', "weren't", 'was', 'up', 'during', 'after', 'me', 'into', 'from', 'further', 't', "mustn't", 'just', 'am', "you're", "she's", 'as', 'own', 're', 'an', 'him', 'when', 'all', 'with', 'by', 'ours', 'll', 'i', 'out', 'their', 'so', "isn't", 'your', 'that', 'down', 'other', "that'll", 've', 'were', 'wouldn', 'isn', 'it', 'yourself', 'between'],
    ];

    public function getFieldsFormObjects(): array
    {
        $language = new FieldForm($this->createLabel('language'), $this->getLanguage());
        $stopset = new FieldForm($this->createLabel('stopset'), $this->getStopSet());

        $sets = [];
        foreach ($this->sets as $name => $set) {
            $sets[] = new FieldForm('', $this->createSet($name, $set));
        }

        return array_merge([$language, $stopset], $sets);
    }

    protected function getLanguage()
    {
        return \Form::select($this->createName('language'), [
            'none' => __($this->valueLabels['none']),
            'rus' => $this->valueLabels['rus'],
            'en' => $this->valueLabels['en'],

        ], $this->component->language ?? 'rus', ['class' => $this->class . ' stop-words select-language']);
    }

    protected function getStopSet()
    {
        $htmlOptions = ['class' => $this->class . ' stop-words stop-words-remover'];
        if ($preset = $this->sets[$this->component->language ?? 'rus']) {
            $htmlOptions['disabled'] = 'disabled';
        }

        return \Form::textarea($this->createName('stopset'), $this->component->stopset ?? implode(',', $preset), $htmlOptions);
    }

    private function createSet(string $name, array $set)
    {
        return \Form::hidden($name, implode(', ', $set), ['class' => ' stop-words stop-sets lang-' . $name]);
    }
}
