<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use Illuminate\Validation\Validator;

class TextNormalizer extends Component
{
    protected $attributes = ['norm_method', 'tokenizer'];

    protected $values = [
        'norm_method' => ['lemmatize', 'stem', 'none'],
        'tokenizer' => ['treebank', 'word_tokenize'],
    ];

    public function description(): string
    {
        return 'Нормализатор текста. Удаляет неинформативный текст (ссылки, e-mail адреса, числа). Приводит слова к нормальной форме.';
    }

    public function validate($data): bool
    {
        /** @var Validator $validator */
        $validator = Validator::make($data, [
            'norm_method' => ['required', Rule::in($this->values['norm_method'])],
            'tokenizer' => ['required', Rule::in($this->values['tokenizer'])],
        ]);
        return $validator->passes();
    }

    public function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\TextNormalizer($this))->getFieldsFormObjects();
    }
}
