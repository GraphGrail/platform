<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


use App\Domain\Exception\Exception;

class Provider implements StrategyProvider
{

    protected $list = [];

    public function __construct(array $strategies = [])
    {
        foreach ($strategies as $params) {
            $class = $params['class'];
            unset($params['class']);

            if (!class_exists($class)) {
                \Log::warning("Strategy doesn't exist: {$class}");
                continue;
            }
            $this->list[] = new $class($params);
        }
    }

    public function all(): array
    {
        return $this->list;
    }

    /**
     * @param string $class
     * @return Strategy
     * @throws Exception
     */
    public function get(string $class): Strategy
    {
        foreach ($this->all() as $item) {
            if (\get_class($item) === $class) {
                return $item;
            }
        }
        throw new Exception(sprintf("Provider `%s` doesn't provide {$class} strategy", \get_class($this)));
    }
}
