<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov;


use App\Domain\AiModel;
use App\Domain\Component;
use App\Domain\Configuration;
use App\Domain\Dataset\Dataset;
use App\Domain\Dataset\Storage;
use App\Domain\Strategy\iPavlov\Component\StopWordsRemover;
use App\Domain\Strategy\iPavlov\Component\TextNormalizer;
use App\Domain\Strategy\Result;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Validation\ValidationException;
use \RuntimeException;

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

    /**
     * @param AiModel $model
     * @return \App\Domain\Strategy\Strategy
     * @throws \App\Domain\Exception\ConfigurationException
     */
    public function verification(AiModel $model): \App\Domain\Strategy\Strategy
    {
        $config = $this->createJsonConfiguration($model->configuration);
        $requestData = [
            'model' => $model->id,
            'config' => $config,
        ];

        $response = $this->client->post('/checkConfig', [
            RequestOptions::JSON => $requestData,
        ]);
        $contents = $response->getBody()->getContents();
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException($contents);
        }
        $model->status = AiModel::STATUS_VERIFY_CONFIG_FAIL;

        if (false !== strpos($contents, 'ok')) {
            $model->status = AiModel::STATUS_VERIFY_CONFIG_OK;
        }
        $model->save();

        return $this;
    }

    public function train(AiModel $model, Dataset $dataset): \App\Domain\Strategy\Strategy
    {
        $config = $this->createJsonConfiguration($model->configuration);
        $requestData = [
            'model' => $model->id,
            'dataset' => (new Storage())->getPath($dataset),
            'config' => $config,
        ];

        $response = $this->client->post('/learn', [
            RequestOptions::JSON => $requestData,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException($response->getBody()->getContents());
        }

        $model->status = AiModel::STATUS_TRAINING;
        $model->save();

        return $this;
    }

    public function status(AiModel $model): \App\Domain\Strategy\Strategy
    {
        $response = $this->client->get('/status', [
            RequestOptions::JSON => ['model' => $model->id],
        ]);
        $content = $response->getBody()->getContents();
        if (false !== mb_strpos($content, self::RESPONSE_STATUS_READY)) {
            $model->status = AiModel::STATUS_READY;
        }
        if (false !== mb_strpos($content, self::RESPONSE_STATUS_LEARNING)) {
            $model->status = AiModel::STATUS_READY;
        }

        $model->save();
        return $this;
    }

    public function exec(AiModel $model, $data = null): Result
    {
        if ($model->status === AiModel::STATUS_VERIFY_CONFIG_OK) {
            return $this->startTrain($model);
        }
        if ($model->status === AiModel::STATUS_TRAINED) {
            return $this->startTesting($model);
        }
        if ($model->status === AiModel::STATUS_READY) {
            return $this->doRequest($model, $data);
        }
        throw new RuntimeException('The model not ready: ' . $model->statusLabel());
    }

    protected function doRequest(AiModel $model, $data)
    {
        $response = $this->client->post('/exec', [
            RequestOptions::JSON => ['model' => $model->id, 'data' => $data],
        ]);
        $result = new Result($data, $response->getBody()->getContents());
        $this->logResult($model, $result);
        return $result;
    }

    public function name(): string
    {
        return 'iPavlov';
    }

    /**
     * @param Component[] $components
     * @param array $data
     * @return mixed|void
     * @throws ValidationException
     */
    public function validate(array $components, array $data = [])
    {
        if ($data) {
            foreach ($components as $component) {
                $component->validate($data[$component::name()]);
            }
        }

        $this->validatePositions($components);
    }

    /**
     * @param Component[] $components
     * @return bool
     * @throws ValidationException
     */
    private function validatePositions(array $components): bool
    {
        $textNormalizerPos = $stopWordsPos = null;

        /** @var Component[] $components */
        $components = array_values($components);
        foreach ($components as $pos => $component) {
            if ($component::name() === TextNormalizer::name()) {
                $textNormalizerPos = $pos;
            }
            if ($component::name() === StopWordsRemover::name()) {
                $stopWordsPos = $pos;
            }
        }
        if (null === $textNormalizerPos) {
            return true;
        }
        if (null === $stopWordsPos) {
            return true;
        }

        $position = $textNormalizerPos - $stopWordsPos;

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['position' => abs($position)], [
            'position' => 'required|numeric|max:1',
            ],
            ['position.max' => __('"Text normalizer" and "Stop words" components may be only near each other')]
        );
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->passes();
    }


    /**
     * @param Configuration $configuration
     * @return array
     * @throws \App\Domain\Exception\ConfigurationException
     */
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

    protected function startTrain(AiModel $model): Result
    {
        $this->train($model, $model->dataset);
        return new Result();
    }

    private function startTesting(AiModel $model): Result
    {
        $model->status = AiModel::STATUS_READY;
        $model->save();
        return new Result();
    }

    private function logResult(AiModel $model, Result $result)
    {
        $log = new AiModel\Stat([
            'user_id' => $model->user_id,
            'model_id' => $model->id,
            'result' => $result->getData(),
            'query' => $result->getQuery(),
        ]);
        $log->save();
    }
}
