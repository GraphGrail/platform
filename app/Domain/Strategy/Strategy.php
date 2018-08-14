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
     * @param Configuration|null $hydrator
     * @return Component[]
     */
    public function getComponents(Configuration $hydrator = null): array
    {
        if (!$hydrator) {
            return $this->components;
        }
        foreach ($this->components as $component) {
            $hydrator->fillComponent($component);
        }
        return $this->components;
    }

    abstract public function name(): string;
    abstract public function exec(AiModel $model, $data = null): Result;

    abstract public function status(AiModel $model): Strategy;

    /**
     * @param Component[] $components
     * @param array $data
     * @return mixed
     */
    abstract public function validate(array $components, array $data = []);

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
