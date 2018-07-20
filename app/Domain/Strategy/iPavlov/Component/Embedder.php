<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use Illuminate\Validation\Validator;

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

    public function description(): string
    {
        return 'Преобразователь текста в признаковое пространство, т.е. в вид, приемлемый для обработки AI алгоритмом';
    }

    public function validate($data): bool
    {
        /** @var Validator $validator */
        $validator = Validator::make($data, [
            'emb_type' => ['required', Rule::in($this->values['emb_type'])],
        ]);
        return $validator->passes();
    }

    function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\Embedder())->getFieldsFormObjects();
    }
}
