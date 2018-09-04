<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Validator;

class Embedder extends Component
{
    protected $attributes = ['emb_type'];
    protected $values = [
        'emb_type' => [
            'pretrained_compressed',
            'pretrained_ft',
            'pretrained_w2v',
            'acquired_w2v',
            'acquired_ft',
            'bow',
            'num_sequence',
        ],
    ];

    public static function name(): string
    {
        return 'embedder';
    }

    public function description(): string
    {
        return __('Choose Feature Engineering type. Use default settings if you are unfamiliar with it.');
    }

    /**
     * @param $data
     * @return bool
     * @throws ValidationException
     */
    public function validate($data): bool
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($data, [
            'emb_type' => ['required', Rule::in($this->values['emb_type'])],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return true;
    }

    function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\Embedder($this))->getFieldsFormObjects();
    }

    public function jsonSerialize()
    {
        return array_merge([
            'name' => self::name(),
            'in' => ['x'],
            'out' => ['xv'],
            'load_path' => [
                'ft_compressed.pkl',
                'ft_compressed_local.pkl',
            ],
            'emb_dim' => 15,
            'emb_len' => 25,
        ], $this->createParams());
    }
}
