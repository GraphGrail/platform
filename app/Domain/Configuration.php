<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain;


use App\Domain\Configuration\ComponentRelation;
use App\Domain\Exception\ConfigurationException;
use App\Domain\Strategy\Strategy;
use App\Domain\Strategy\StrategyProvider;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Configuration
 * @package App\Domain
 * @property integer id
 * @property integer user_id
 * @property string strategy_class
 * @property AiModel[] models
 * @property ComponentRelation[] componentRelations
 */
class Configuration extends Model
{
    protected $_components;

    protected $fillable = ['user_id', 'strategy_class'];

    public function componentRelations()
    {
        return $this->hasMany(ComponentRelation::class);
    }

    /**
     * @param bool $refresh
     * @return Component[]
     * @throws ConfigurationException
     */
    public function components($refresh = false): array
    {
        if ($refresh) {
            $this->_components = null;
        }
        if (null !== $this->_components) {
            return $this->_components;
        }
        /** @var ComponentRelation[] $list */
        $list = $this->componentRelations()->get()->all();
        foreach ($list as $model) {
            if (!class_exists($model->component_class)) {
                \Log::warning("Component class doesn't exist: {$model->component_class}");
                continue;
            }
            if (!is_subclass_of($model->component_class, Component::class)) {
                \Log::warning("Wrong Component class: {$model->component_class}");
                continue;
            }
            $this->_components[$model->component_class] = new $model->component_class($this->strategy(), $model->component_attributes);
        }

        return $this->_components ?? [];
    }

    public function models()
    {
        return $this->hasMany(AiModel::class);
    }

    /**
     * @return Strategy|null
     * @throws ConfigurationException
     */
    public function strategy(): ?Strategy
    {
        if (!$this->strategy_class) {
            return null;
        }
        if (!class_exists($this->strategy_class)) {
            throw new ConfigurationException("Strategy class doesn't exist: {$this->strategy_class}");
        }
        /** @var StrategyProvider $provider */
        $provider = app()->get(StrategyProvider::class);
        return $provider->get($this->strategy_class);
    }

    public function fillComponent(Component $component): Configuration
    {
        if (!$exist = $this->components()[\get_class($component)]) {
            return $this;
        }
        $component->setRawAttributes($exist->getAttributes());
        return $this;
    }
}
