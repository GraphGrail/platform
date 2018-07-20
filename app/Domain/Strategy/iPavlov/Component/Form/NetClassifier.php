<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Form;


use App\Domain\Strategy\Component\Form\FieldForm;
use Illuminate\Support\HtmlString;

class NetClassifier
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

        'classes' => 'Путь к pickle-файлу с именми классов',
        'save_path' => 'Путь для сохранения весовых коэффициентов ИНС',
        'load_path' => 'Путь для загрузки весовых коэффициентов ИНС',

        'layers[activation]' => 'Функиция активации',
        'layers[units]' => 'Units',
        'layers[kernel_size]' => 'Размер ядра',
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
        $result = ['<div class="component-field-layers component-field-repeatable">'];

        $fields = [];

        $fields[] = [
            $this->createLabel('layers[units]'),
            \Form::number('layers[units]', 1024),
        ];

        $fields[] = [
            $this->createLabel('layers[kernel_size]'),
            \Form::number('layers[kernel_size]', 2),
        ];

        $fields[] = [
            $this->createLabel('layers[activation]'),
            \Form::number('layers[activation]', 'relu'),
        ];
        $result[] = implode('<br>', array_map(function ($data) {return implode('', $data);}, $fields));

        $result[] = '</div>';

        return new HtmlString(implode('', $result));
    }

    protected function get_architecture()
    {
        return \Form::select('architecture', $this->variants['architecture'], 'dcnn');
    }

    protected function get_loss()
    {
        return \Form::select('loss', $this->variants['loss'], 'categorical_crossentropy');
    }

    protected function get_metrics()
    {
        return \Form::select('metrics', $this->variants['metrics'], 'categorical_accuracy');
    }

    protected function get_optimizer()
    {
        return \Form::select('optimizer', $this->variants['optimizer'], 'adam');
    }

    protected function get_emb_dim()
    {
        return \Form::number('emb_dim', 25);
    }

    protected function get_seq_len()
    {
        return \Form::number('seq_len', 50);
    }

    protected function get_pool_size()
    {
        return \Form::number('pool_size', 4);
    }

    protected function get_dropout_power()
    {
        return \Form::number('dropout_power', 0.5);
    }

    protected function get_l2_power()
    {
        return \Form::number('l2_power', 1e-4);
    }

    protected function get_n_classes()
    {
        return \Form::number('n_classes', 5);
    }

    protected function get_classes()
    {
        return \Form::text('classes');
    }

    protected function get_save_path()
    {
        return \Form::text('save_path');
    }

    protected function get_load_path()
    {
        return \Form::text('load_path');
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function createLabel($name)
    {
        return \Form::label($name, $this->labels[$name] ?? $name);
    }
}
