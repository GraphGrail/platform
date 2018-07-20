<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov;


use App\Domain\AiModel;
use App\Domain\Configuration;
use App\Domain\Dataset\Dataset;
use App\Domain\Strategy\Result;

class Strategy extends \App\Domain\Strategy\Strategy
{

    public function __construct(array $params = [])
    {
        foreach ($params['components'] as $class) {
            $this->components[] = new $class($this);
        }
    }

    public function learn(AiModel $model, Dataset $dataset): \App\Domain\Strategy\Strategy
    {
        $config = $this->createJsonConfiguration($model->configuration);


        return $this;
    }

    public function exec(AiModel $model): Result
    {
        // TODO: Implement exec() method.
    }

    public function name(): string
    {
        return 'iPavlov';
    }

    protected function createJsonConfiguration(Configuration $configuration): array
    {
        $pipe = [];
        foreach ($configuration->components() as $component) {
            $pipe[] = $component->toJson();
        }

        return [
            'deeppavlov_root' => '',
            'dataset_reader' =>
                [
                    'name' => 'basic_classification_reader',
                    'data_path' => 'data/',
                    'class_sep' => '________',
                ],
            'dataset_iterator' =>
                [
                    'name' => 'basic_classification_iterator',
                    'seed' => 42,
                    'fields_to_merge' =>
                        [
                            'train',
                            'valid',
                        ],
                    'merged_field' => 'train',
                    'field_to_split' => 'train',
                    'split_fields' =>
                        [
                            'train',
                            'valid',
                        ],
                    'split_proportions' =>
                        [
                            0.90000000000000002,
                            0.10000000000000001,
                        ],
                ],
            'chainer' =>
                [
                    'pipe' => $pipe,
                    'out' =>
                        [
                            'y_pred',
                        ],
                    'in' =>
                        [
                            'x',
                        ],
                    'in_y' =>
                        [
                            'y',
                        ],
                ],
            'train' =>
                [
                    'validation_patience' => 10000,
                    'epochs' => 10000,
                    'batch_size' => 32,
                    'metrics' =>
                        [
                            'sets_accuracy',
                        ],
                    'val_every_n_epochs' => 1,
                    'log_every_n_epochs' => 1,
                ],
        ];
    }
}
