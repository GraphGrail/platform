<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


use App\Domain\AiModel;
use App\Domain\Component;
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

    abstract public function name(): string;
    abstract public function exec(AiModel $model, $data = null): Result;

    abstract public function status(AiModel $model): Strategy;

    public function getForm(string $selected = null)
    {
        $name = \get_class($this);

        return new HtmlString(\Form::label('ipavlov', $this->name()) . \Form::radio('strategy', $name, $name === $selected, ['id' => 'ipavlov']));
    }

    public function getFormName(): string
    {
        return \get_class($this);
    }
}
