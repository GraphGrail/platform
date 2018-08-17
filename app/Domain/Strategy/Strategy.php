<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


use App\Domain\AiModel;
use App\Domain\Component;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

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

    abstract public function verification(AiModel $model): Strategy;
    abstract public function status(AiModel $model): Strategy;

    /**
     * @param Component[] $components
     * @param array $data
     * @throws ValidationException
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
