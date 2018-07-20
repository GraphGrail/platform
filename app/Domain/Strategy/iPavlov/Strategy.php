<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov;


use App\Domain\AiModel;
use App\Domain\Configuration;
use App\Domain\Strategy\Result;

class Strategy extends \App\Domain\Strategy\Strategy
{

    public function __construct(array $params = [])
    {
        foreach ($params['components'] as $class) {
            $this->components[] = new $class($this);
        }
    }

    public function learn(Configuration $configuration): AiModel
    {
        // TODO: Implement learn() method.
    }

    public function exec(AiModel $model): Result
    {
        // TODO: Implement exec() method.
    }

    public function name(): string
    {
        return 'iPavlov';
    }
}
