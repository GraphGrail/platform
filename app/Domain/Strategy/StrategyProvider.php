<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy;


interface StrategyProvider
{
    /**
     * @return Strategy[]
     */
    public function all(): array;

    public function get(string $class): Strategy;
}
