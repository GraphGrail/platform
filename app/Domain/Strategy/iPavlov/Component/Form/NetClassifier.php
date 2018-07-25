<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\ComponentForm;
use App\Domain\Strategy\Component\Form\FieldForm;
use Illuminate\Support\HtmlString;

class NetClassifier extends ComponentForm
{
    protected $labels = [
        'architecture' => 'Тип архитектуры ИНС',
        'loss' => 'Тип функции потерь',
        'metrics' => 'Тип метрики качества (применяется только в процессе обучения)',
        'optimizer' => 'Обучающий алгоритм',
        'layers' => 'Количество и параметры слоев ИНС (для cnn архитектуры)',
        'emb_dim' => 'Размерность признакового пространства',
        'seq_len' => 'Фиксированная длина последовательности векторов',
        'pool_size' => 'Мощность пулинга',
        'dropout_power' => 'Мощность dropout регуляризации',
        'l2_power' => 'Мощность L2 регуляризации',
        'n_classes' => 'Количество классифицируемых классов',

//        'classes' => 'Путь к pickle-файлу с именми классов',
//        'save_path' => 'Путь для сохранения весовых коэффициентов ИНС',
//        'load_path' => 'Путь для загрузки весовых коэффициентов ИНС',

        'layers_type' => 'Тип архитектуры для слоя',
        'layers_activation' => 'Функиция активации',
        'layers_units' => 'Кол-во нейронов',
        'layers_kernel_size' => 'Размер ядра',
    ];

    protected $valueLabels = [
        'bigru',
        'dcnn',
        'dense',
        'categorical_crossentropy',
        'crossentropy',
        'categorical_hinge',
        'hinge',
        'categorical_accuracy',
        'accuracy',
        'adam',
        'rmsprop',
        'SGD',
        'momentum',
    ];

    protected $variants = [
        'architecture' => [
            'bigru' => 'bigru',
            'dcnn' => 'dcnn',
            'dense' => 'dense',
        ],
        'loss' => [
            'categorical_crossentropy' => 'categorical_crossentropy',
            'crossentropy' => 'crossentropy',
            'categorical_hinge' => 'categorical_hinge',
            'hinge' => 'hinge',
        ],
        'metrics' => [
            'categorical_accuracy' => 'categorical_accuracy',
            'accuracy' => 'accuracy',
        ],
        'optimizer' => [
            'adam' => 'adam',
            'rmsprop' => 'rmsprop',
            'SGD' => 'SGD',
            'momentum' => 'momentum',
        ],
        'activation' => [
            'relu' => 'relu',
            'sigmoid' => 'sigmoid',
            'tanh' => 'tanh',
        ],
        'layers' => [
            'bilstm_layers' => 'bilstm',
            'conv_layers' => 'conv',
        ],
    ];

    public function getFieldsFormObjects(): array
    {
        $list = [];
        foreach ($this->labels as $attribute => $label) {
            $method = 'get_' . $attribute;
            if (!method_exists($this, $method)) {
                continue;
            }
            $list[] = new FieldForm($this->createLabel($attribute), $this->$method());
        }
        return $list;
    }

    protected function get_layers()
    {
        $result = [];
        foreach ($this->component->layers_arch as $index => $layers_arch) {
            $data = [
                'layers_arch' => $this->component->layers_arch[$index],
                'layers_units' => $this->component->layers_units[$index],
                'layers_kernel_size' => $this->component->layers_kernel_size[$index],
                'layers_activation' => $this->component->layers_activation[$index],
            ];
            $result[] = implode('', $this->createLayer($data, $index === (\count($this->component->layers_arch) - 1)));
        }

        return new HtmlString(implode('', $result));
    }

    protected function get_architecture()
    {
        return \Form::select($this->createName('architecture'), $this->variants['architecture'], $this->component->architecture ?? 'dcnn', ['class' => $this->class]);
    }

    protected function get_loss()
    {
        return \Form::select($this->createName('loss'), $this->variants['loss'], $this->component->loss ?? 'categorical_crossentropy', ['class' => $this->class]);
    }

    protected function get_metrics()
    {
        return \Form::select($this->createName('metrics'), $this->variants['metrics'], $this->component->metrics ?? 'categorical_accuracy', ['class' => $this->class]);
    }

    protected function get_optimizer()
    {
        return \Form::select($this->createName('optimizer'), $this->variants['optimizer'], $this->component->optimizer ?? 'adam', ['class' => $this->class]);
    }

    protected function get_emb_dim()
    {
        return \Form::number($this->createName('emb_dim'), $this->component->emb_dim ?? 25, ['class' => $this->class]);
    }

    protected function get_seq_len()
    {
        return \Form::number($this->createName('seq_len'), $this->component->seq_len ?? 50, ['class' => $this->class]);
    }

    protected function get_pool_size()
    {
        return \Form::number($this->createName('pool_size'), $this->component->pool_size ?? 4, ['class' => $this->class]);
    }

    protected function get_dropout_power()
    {
        return \Form::number($this->createName('dropout_power'), $this->component->dropout_power ?? 0.5, ['class' => $this->class]);
    }

    protected function get_l2_power()
    {
        return \Form::number($this->createName('l2_power'), $this->component->l2_power ?? 1e-4, ['class' => $this->class]);
    }

    protected function get_n_classes()
    {
        return \Form::number($this->createName('n_classes'), $this->component->n_classes ?? 5, ['class' => $this->class]);
    }

    protected function get_classes()
    {
        return \Form::text($this->createName('classes'), $this->component->classes ?? '', ['class' => $this->class]);
    }

    protected function get_save_path()
    {
        return \Form::text($this->createName('save_path'), $this->component->save_path ?? '', ['class' => $this->class]);
    }

    protected function get_load_path()
    {
        return \Form::text($this->createName('load_path'), $this->component->load_path ?? '', ['class' => $this->class]);
    }

    /**
     * @return array
     */
    protected function createLayer($data, $addBtn = true): array
    {
        $result = ['<div class="component-field-layers component-field-repeatable alert m-alert m-alert--default">'];

        $fields = [];

        $fields[] = [
            $this->createLabel('layers_type'),
            \Form::select($this->createName('layers_arch') . '[]', $this->variants['layers'], $data['layers_arch'] ?? 'bilstm_layers', ['class' => $this->class]),
        ];

        $fields[] = [
            $this->createLabel('layers_units'),
            \Form::number($this->createName('layers_units') . '[]', $data['layers_units'] ?? 1024, ['class' => $this->class]),
        ];

        $fields[] = [
            $this->createLabel('layers_kernel_size'),
            \Form::number($this->createName('layers_kernel_size') . '[]', $data['layers_kernel_size'] ?? 2, ['class' => $this->class]),
        ];

        $fields[] = [
            $this->createLabel('layers_activation'),
            \Form::select($this->createName('layers_activation') . '[]', $this->variants['activation'], $data['layers_activation'] ?? 'relu', ['class' => $this->class]),
        ];
        $result[] = implode('', array_map(function ($data) {
            return implode('', $data);
        }, $fields));

        $result[] = '<div class="m-demo" style="margin-bottom: 0px"><div class="m-demo__preview  m-demo__preview--btn" style="background-color: #f7f8fa; padding-bottom: 0px">';
        $result[] = \Form::button('+ ' . __('Add'), ['class' => 'btn m-btn--pill m-btn--air btn-info add-repeatable ' . ($addBtn ? '': 'm--hide')]);
        $result[] = \Form::button('– ' . __('Remove'), ['class' => 'btn m-btn--pill m-btn--air remove-repeatable']);
        $result[] = '</div></div>';

        $result[] = '</div>';
        return $result;
    }
}
