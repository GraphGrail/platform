<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Validator;

class TextNormalizer extends Component
{
    protected $attributes = ['norm_method', 'tokenizer', 'optional'];

    protected $values = [
        'norm_method' => ['lemmatize', 'stem', 'none'],
        'tokenizer' => ['treebank', 'word_tokenize'],
    ];

    public static function name(): string
    {
        return 'text_normalizer';
    }

    public function description(): string
    {
        return 'Нормализатор текста. Удаляет неинформативный текст (ссылки, e-mail адреса, числа). Приводит слова к нормальной форме.';
    }

    /**
     * @param $data
     * @return bool
     * @throws ValidationException
     */
    public function validate($data): bool
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make($data, [
            'norm_method' => ['required', Rule::in($this->values['norm_method'])],
            'tokenizer' => ['required', Rule::in($this->values['tokenizer'])],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->passes();
    }

    public function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\TextNormalizer($this))->getFieldsFormObjects();
    }

    public function jsonSerialize()
    {
        return array_merge([
            'name' => self::name(),
            'id' => self::name(),
            'in' => ['x'],
            'out' => ['xn'],
        ], $this->createParams());
    }
}
