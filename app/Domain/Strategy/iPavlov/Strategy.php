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
use App\Domain\Exception\ExecutionException;
use App\Domain\Exception\VerificationException;
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
    protected const STATUS_NOT_TRAINED = 'not_train';
    protected const STATUS_TRAINING    = 'in_train';
    protected const STATUS_TRAINED     = 'trained';
    protected const STATUS_ERROR       = 'error';

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
            'verify' => false,
        ];
        $this->client = new Client($guzzle);
    }

    /**
     * @param AiModel $model
     * @return \App\Domain\Strategy\Strategy
     * @throws ValidationException
     * @throws \App\Domain\Exception\ConfigurationException
     */
    public function verification(AiModel $model): \App\Domain\Strategy\Strategy
    {
        $errors = [];
        try {
            $config = $this->createJsonConfiguration($model->configuration);
            $requestData = [
                'config' => $config,
            ];

            $response = $this->client->post('check_config', [
                RequestOptions::JSON => $requestData,
            ]);
            $contents = $response->getBody()->getContents();
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException($contents);
            }

            if (!$contents = json_decode($contents, true)) {
                \Log::error($response->getBody()->getContents());
                throw new VerificationException('Invalid response');
            }
            if (!array_key_exists('status', $contents)) {
                \Log::error($response->getBody()->getContents());
                throw new VerificationException('Invalid response');
            }
            if (false === strpos($contents['status'], 'ok')) {
                throw new VerificationException($contents['errors']);
            }
            $model->status = AiModel::STATUS_VERIFY_CONFIG_OK;

        } catch (VerificationException $e) {
            \Log::error($e->getMessage());
            $errors = $e->getErrors();

            $model->status = AiModel::STATUS_VERIFY_CONFIG_FAIL;
        }
        $model->save();

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['errors' => $errors], [
            'errors' => 'size:0',
        ], ['errors.size' => __('Configuration failed: ' . implode(', ', $errors))]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }

    /**
     * @param AiModel $model
     * @param Dataset $dataset
     * @return \App\Domain\Strategy\Strategy
     * @throws ValidationException
     * @throws \App\Domain\Exception\ConfigurationException
     */
    public function train(AiModel $model, Dataset $dataset): \App\Domain\Strategy\Strategy
    {
        $errors = [];
        try {
            $contents = $this->requestModelStatus($model);
            $status = $contents['status'];
            if ($status === self::STATUS_TRAINING) {
                return $this;
            }

            $config = $this->createJsonConfiguration($model->configuration);
            $requestData = [
                'dataset' => (new Storage())->getPath($dataset),
                'config' => $config,
            ];

            $response = $this->client->post('train/' . $model->id, [
                RequestOptions::JSON => $requestData,
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new ExecutionException($response->getBody()->getContents());
            }

            $contents = $response->getBody()->getContents();
            if (!$contents = json_decode($contents, true)) {
                \Log::error($response->getBody()->getContents());
                throw new ExecutionException('Invalid response');
            }
            if (!array_key_exists('status', $contents)) {
                \Log::error($response->getBody()->getContents());
                throw new ExecutionException('Invalid response');
            }
            if (false === strpos($contents['status'], 'ok')) {
                throw new ExecutionException($contents['errors']);
            }

            $model->status = AiModel::STATUS_TRAINING;
        } catch (ExecutionException $e) {
            $model->status = AiModel::STATUS_TEST_FAIL;

            \Log::error($e->getMessage());
            $errors = $e->getErrors();
        }
        $model->performance = 0;
        $model->save();

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['errors' => $errors], [
            'errors' => 'size:0',
        ], ['errors.size' => __('Failed to start train model: ' . implode(', ', $errors))]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }

    public function stop(AiModel $model): \App\Domain\Strategy\Strategy
    {
        $errors = [];
        try {

            $response = $this->client->post('stop/' . $model->id);
            if ($response->getStatusCode() !== 200) {
                throw new ExecutionException($response->getBody()->getContents());
            }

            $contents = $response->getBody()->getContents();
            if (!$contents = json_decode($contents, true)) {
                \Log::error($response->getBody()->getContents());
                throw new ExecutionException('Invalid response');
            }

            $model->status = AiModel::STATUS_NEW;
            $model->performance = 0;
        } catch (ExecutionException $e) {
            \Log::error($e->getMessage());
            $errors = $e->getErrors();
        }
        $model->save();

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['errors' => $errors], [
            'errors' => 'size:0',
        ], ['errors.size' => __('Failed to stop training model: ' . implode(', ', $errors))]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }


    public function status(AiModel $model): \App\Domain\Strategy\Strategy
    {
        $errors = [];
        try {
            $contents = $this->requestModelStatus($model);
            $status = $contents['status'];

            if ($status === self::STATUS_ERROR) {
                $model->status = AiModel::STATUS_TEST_FAIL;

                $errors = $contents['errors'];
                $errors[] = $contents['backtrace'] ?? [];

                $model->setErrors($errors);
                $model->save();
                throw new ExecutionException($contents['errors'] ?? __('Check status error'));
            }
            if ($status === self::STATUS_NOT_TRAINED) {
                $model->status = AiModel::STATUS_NEW;
            }
            if ($status === self::STATUS_TRAINING) {
                $model->status = AiModel::STATUS_TRAINING;
                $model->performance = $contents['perfomance'] ?? 0;
            }
            if ($status === self::STATUS_TRAINED) {
                $model->status = AiModel::STATUS_READY;
                $model->performance = $contents['perfomance'] ?? 0;
            }
        } catch (ExecutionException $e) {
            $errors = $e->getErrors();
        }

        $model->save();

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make(['errors' => $errors], [
            'errors' => 'size:0',
        ], ['errors.size' => __('Failed to get status of model: ' . implode(', ', $errors))]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $this;
    }

    /**
     * @param AiModel $model
     * @param null $data
     * @return Result
     * @throws \App\Domain\Exception\ConfigurationException
     */
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

    protected function doRequest(AiModel $model, $data): Result
    {
        $response = $this->client->post('run/' . $model->id, [
            RequestOptions::JSON => ['message' => $data],
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
            $pipe[] = $component->buildConfig();
        }

        return [
            'deeppavlov_root' => '',
            'model_path' => '',
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
                            'test',
                        ],
                    'split_proportions' =>
                        [
                            0.80000000000000002,
                            0.10000000000000001,
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
                    'epochs' => 5,
                    'batch_size' => 32,
                    'metrics' =>
                        [
                            'classification_f1',
                        ],
                    'val_every_n_epochs' => 1,
                    'log_every_n_epochs' => 1,
                    'tensorboard_log_dir' => 'logs/',
                ],
        ];
    }

    /**
     * @param AiModel $model
     * @return Result
     * @throws \App\Domain\Exception\ConfigurationException
     */
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

    /**
     * @param AiModel $model
     * @return mixed|string
     * @throws ExecutionException
     */
    protected function requestModelStatus(AiModel $model)
    {
        $response = $this->client->get('status/' . $model->id);
        if ($response->getStatusCode() !== 200) {
            throw new ExecutionException($response->getBody()->getContents());
        }

        $contents = $response->getBody()->getContents();
        if (!$contents = json_decode($contents, true)) {
            \Log::error($response->getBody()->getContents());
            throw new ExecutionException('Invalid response');
        }
        if (!array_key_exists('status', $contents)) {
            \Log::error($response->getBody()->getContents());
            throw new ExecutionException('Invalid response');
        }
        return $contents;
    }
}
