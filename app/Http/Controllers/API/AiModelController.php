<?php

namespace App\Http\Controllers\API;

use App\Domain\AiModel;
use App\Domain\Exception\ConfigurationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AiModelController extends Controller
{
    /**
     * @param AiModel $model
     * @param Request $request
     * @return Response
     * @throws ConfigurationException
     * @throws \InvalidArgumentException
     */
    public function exec(AiModel $model, Request $request)
    {
        if (!$data = $request->get('data')) {
            throw new \InvalidArgumentException('Empty data for execution');
        }

        if (!$strategy = $model->configuration->strategy()) {
            throw new ConfigurationException('Configuration not complete');
        }
        return new Response($strategy->exec($model, $data)->getData());
    }
}
