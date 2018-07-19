<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


use App\Domain\AiModel;
use App\Domain\Component;
use App\Domain\Configuration;

abstract class Strategy
{
    protected $components = [];

    /**
     * @return Component[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    abstract public function learn(Configuration $configuration): AiModel;
    abstract public function exec(AiModel $model): Result;
    abstract public function name(): string;
}
