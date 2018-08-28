<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use App\Domain\Strategy\iPavlov\Component\Validator\NetClassifier\LayersRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Validator;

/**
 * @property string architecture
 * @property mixed loss
 * @property mixed optimizer
 * @property mixed metrics
 * @property mixed l2_power
 * @property array layers
 * @property mixed emb_dim
 * @property mixed seq_len
 * @property mixed pool_size
 * @property mixed dropout_power
 * @property mixed n_classes
 * @property mixed classes
 */
class NetClassifier extends Component
{
    protected $attributes = [
        'architecture', 'loss', 'metrics', 'optimizer', 'layers', 'emb_dim', 'seq_len', 'pool_size', 'dropout_power',
        'l2_power', 'n_classes', 'classes', 'save_path', 'load_path',
    ];
    protected $values = [
        'architecture' => [
//            'bigru',
//            'dcnn',
//            'cnn',
//            'dense',
            'dual_bilstm_cnn_model',
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

    protected $arch_layers = [
        'dual_bilstm_cnn_model' => [
            'bilstm_layers',
            'conv_layers',
        ],
        'bigru' => [ //todo
            'bilstm_layers',
            'conv_layers',
        ],
        'dcnn' => [
            'bilstm_layers',
            'conv_layers',
        ],
        'cnn' => [
            'bilstm_layers',
            'conv_layers',
        ],
        'dense' => [
            'bilstm_layers',
            'conv_layers',
        ],
    ];

    public static function name(): string
    {
        return 'cnn_model';
    }

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

    function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\NetClassifier($this))->getFieldsFormObjects();
    }

    public function jsonSerialize()
    {
        return [
            'name' => self::name(),
            'in' => ['xv'],
            'in_y' => ['y'],
            'out' => ['y_pred'],

            'architecture_name' => $this->architecture,
            'loss' => $this->loss,
            'metrics' => [$this->metrics],
            'optimizer' => $this->optimizer,
            'architecture_params' => array_merge($this->createLayers($this->architecture),[
                'emb_dim' => (float)$this->emb_dim,
                'seq_len' => (float)$this->seq_len,
                'pool_size' => (float)$this->pool_size,
                'dropout_power' => (float)$this->dropout_power,
                'new2old' => 'new2old.pkl',
            ]),
            'classes' => 'class_names.pkl',
            'confident_threshold' => 0.14999999999999999,
            'save_path' => 'cnn_weights.hdf',
            'load_path' => 'cnn_weights.hdf5',
        ];
    }

    protected function createLayers($architecture): array
    {
        $list = [];
        if (!$names = $this->arch_layers[$architecture]) {
            return $list;
        }
        if (!$layers = (array)$this->layers_arch) {
            return $list;
        }

        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            if (0 !== strpos($name, 'layers_')) {
                continue;
            }

            $name = str_replace('layers_', '', $name);
            $attributes[$name] = $value;
        }

        foreach ($layers as $name) {
            $pos = array_search($name, $attributes['arch'], true);
            if ($pos === false) {
                continue;
            }
            unset($attributes['arch'][$pos]);

            $layer = array_map(function ($attribute) use ($pos) {
                if (!array_key_exists($pos, $attribute)) {
                    return null;
                }
                return $attribute[$pos];
            }, $attributes);

            $layer['l2_power'] = (float)$this->l2_power;
            if (array_key_exists('kernel_size', $layer)) {
                $layer['kernel_size'] = (float)$layer['kernel_size'];
            }
            if (array_key_exists('units', $layer)) {
                $layer['units'] = (float)$layer['units'];
            }

            unset($layer['arch']);

            $list[$name][] = $layer;
        }

        return $list;
    }
}
