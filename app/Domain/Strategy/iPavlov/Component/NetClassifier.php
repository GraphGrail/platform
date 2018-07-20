<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use App\Domain\Strategy\iPavlov\Component\Validator\NetClassifier\LayersRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Validator;

class NetClassifier extends Component
{
    protected $attributes = [
        'architecture', 'loss', 'metrics', 'optimizer', 'layers', 'emb_dim', 'seq_len', 'pool_size', 'dropout_power',
        'l2_power', 'n_classes', 'classes', 'save_path', 'load_path',
    ];
    protected $values = [
        'architecture' => [
            'bigru',
            'dcnn',
            'dense',
        ],
        'loss' => [
            'categorical_crossentropy',
            'crossentropy',
            'categorical_hinge',
            'hinge',
        ],
        'metrics' => [
            'categorical_accuracy',
            'accuracy',
        ],
        'optimizer' => [
            'adam',
            'rmsprop',
            'SGD',
            'momentum',
        ],
    ];

    public function description(): string
    {
        return 'Нейросетевой классификатор. Получает на вход векторное представление текста, возвращая предсказанную метку (метки) класса и уровень уверенности.';
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
            'architecture' => ['required', Rule::in($this->values['architecture'])],
            'loss' => ['required', Rule::in($this->values['loss'])],
            'metrics' => ['nullable', Rule::in($this->values['metrics'])],
            'optimizer' => ['required', Rule::in($this->values['optimizer'])],
            'layers' => ['nullable|array', new LayersRule()],
            'emb_dim' => 'nullable|integer',
            'seq_len' => 'nullable|integer',
            'pool_size' => 'nullable|integer',
            'dropout_power' => 'nullable|numeric',
            'l2_power' => 'nullable|numeric',
            'n_classes' => 'nullable|integer',

            //todo paths?
            'classes' => 'nullable|string',
            'save_path' => 'nullable|string',
            'load_path' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return true;
    }

    public function getAttributeForm($attribute): array
    {
        $map = [
            'layers' => [
                [
                    'units' => 1024,
                    'activation' => 'relu',
                    'kernel_size' => 2,
                ]
            ],
        ];

        return $map[$attribute] ?? [];
    }

    function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\NetClassifier($this))->getFieldsFormObjects();
    }
}
