<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StopWordsRemover extends Component
{
    protected $attributes = ['language', 'stopset', 'optional'];

    protected $values = [
        'language' => ['none', 'en', 'rus'],
    ];

    protected $siblings = [
        TextNormalizer::class,
    ];

    public static function name(): string
    {
        return 'stop_words_remover';
    }


    public function description(): string
    {
        return 'Модуль стоп слов';
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
            'language' => [Rule::in($this->values['language'])],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->passes();
    }

    public function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\StopWordsRemover($this))->getFieldsFormObjects();
    }

    public function jsonSerialize()
    {
        $params = array_filter($this->createParams(), function ($value) {
            return $value !== null;
        });

        return array_merge([
            'name' => self::name(),
            'id' => self::name(),
            'in' => ['x'],
            'out' => ['xnr'],
        ], $params);
    }
}
