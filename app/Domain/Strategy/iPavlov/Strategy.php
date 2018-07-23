<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov;


use App\Domain\AiModel;
use App\Domain\Configuration;
use App\Domain\Dataset\Dataset;
use App\Domain\Strategy\Result;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use http\Exception\RuntimeException;

class Strategy extends \App\Domain\Strategy\Strategy
{

    /** @var Client  */
    protected $client;

    /**
     * Strategy constructor.
     * @param array $params
     * @throws \InvalidArgumentException
     */
    protected const RESPONSE_STATUS_LEARNING = 'learning';

    protected const RESPONSE_STATUS_READY = 'ready';

    /**
     * Strategy constructor.
     * @param array $params
     * @throws \InvalidArgumentException
     */
    public function __construct(array $params = [])
    {
        foreach ($params['components'] as $class) {
            $this->components[] = new $class($this);
        }

        $guzzle = [
            'base_uri' => $params['url'],
            'timeout' => $params['timeout'] ?? 60,
        ];
        $this->client = new Client($guzzle);
    }

    public function learn(AiModel $model, Dataset $dataset): \App\Domain\Strategy\Strategy
    {
        $config = $this->createJsonConfiguration($model->configuration);
        $requestData = [
            'model' => $model->id,
            'config' => $config,
        ];

        $response = $this->client->post('/learn', [
            RequestOptions::JSON => $requestData,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException($response->getBody()->getContents());
        }

        $model->status = AiModel::STATUS_LEARNING;
        $model->save();

        return $this;
    }

    public function status(AiModel $model): \App\Domain\Strategy\Strategy
    {
        $response = $this->client->post('/status', [
            RequestOptions::JSON => ['model' => $model->id],
        ]);
        $content = $response->getBody()->getContents();
        if (mb_strpos($content, self::RESPONSE_STATUS_READY)) {
            $model->status = AiModel::STATUS_READY;
        }
        if (mb_strpos($content, self::RESPONSE_STATUS_LEARNING)) {
            $model->status = AiModel::STATUS_READY;
        }

        $model->save();
        return $this;
    }

    public function exec(AiModel $model): Result
    {
        if ($model->status !== AiModel::STATUS_READY) {
            throw new RuntimeException('Model is not ready');
        }
        $response = $this->client->post('/exec', [
            RequestOptions::JSON => ['model' => $model->id],
        ]);
        return new Result($response->getBody()->getContents());
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
