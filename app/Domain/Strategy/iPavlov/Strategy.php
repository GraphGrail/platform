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
use app\Domain\Strategy\iPavlov\Component\Settings;
use App\Domain\Strategy\iPavlov\Component\StopWordsRemover;
use App\Domain\Strategy\iPavlov\Component\TextNormalizer;
use App\Domain\Strategy\Result;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
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
            RequestOptions::TIMEOUT => $params['timeout'] ?? 60,
            RequestOptions::VERIFY => false,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::HEADERS => [
                'GGPlatform-Api-Key' => $params['api_key'],
            ],
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
                throw new VerificationException(
                    sprintf('Invalid status response. Returned status %s', $response->getStatusCode())
                );
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
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            $errors = ['Something went wrong'];

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
                'language' => $dataset->lang ?: 'ru',
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

    /**
     * @param AiModel $model
     * @return \App\Domain\Strategy\Strategy
     * @throws ValidationException
     */
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

    /**
     * @param AiModel $model
     * @return \App\Domain\Strategy\Strategy
     * @throws ValidationException
     */
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
     * @throws ValidationException
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
        $config = config('aimodels-configuration.iPavlov');
        Log::info('Read config: '.var_export($config, true));
        Log::info('Dump config: '.json_encode($config));
        $pipes = [];

        foreach ($configuration->components() as $component) {
            $componentConfig = $component->buildConfig();

            if ($component instanceof Settings) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $config['train'] = \array_replace_recursive($config['train'], $componentConfig);
                continue;
            }

            $pipes[] = $component::name();
            $found = false;
            foreach ($config['chainer']['pipe'] as $k => $pipe) {
                if ($pipe['name'] === $componentConfig['name']) {
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $config['chainer']['pipe'][$k] = \array_replace_recursive($pipe, $componentConfig);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $config['chainer']['pipe'][] = $componentConfig;
            }
        }

        foreach ($config['chainer']['pipe'] as $k => $pipe) {
            if (\in_array($pipe['name'], $pipes, false)) {
                continue;
            }

            unset($config['chainer']['pipe'][$k]);
        }

        return $config;
    }

    /**
     * @param AiModel $model
     * @return Result
     * @throws ValidationException
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

    private function logResult(AiModel $model, Result $result): void
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

    public function createDefaultConfiguration(): Configuration
    {
        $configuration = new Configuration([
            'user_id' => Auth::id(),
            'strategy_class' => \get_class($this),
        ]);

        foreach ($this->getComponents() as $component) {
            if ($component->optional && !$component instanceof TextNormalizer) {
                continue;
            }
            $class = \get_class($component);
            $link = new Configuration\ComponentRelation([
                'component_class' => $class,
            ]);
            $link->component_attributes = $component->getAttributes();
            $configuration->componentRelations[] = $link;
        }

        return $configuration;
    }
}
