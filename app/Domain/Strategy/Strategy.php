<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


use App\Domain\AiModel;
use App\Domain\Component;
use App\Domain\Configuration;
use Illuminate\Support\HtmlString;

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

    public function getForm(string $selected = null)
    {
        $name = \get_class($this);

        return new HtmlString(\Form::label('ipavlov', $this->name()) . \Form::radio('strategy', $name, $name === $selected, ['id' => 'ipavlov']));
    }
}
