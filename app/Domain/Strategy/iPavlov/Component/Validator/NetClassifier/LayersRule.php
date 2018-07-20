<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component\Validator\NetClassifier;


use Illuminate\Contracts\Validation\Rule;

class LayersRule implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!\is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!isset($item['units'])) {
                return false;
            }
            if (!isset($item['activation'])) {
                return false;
            }
            if (!isset($item['kernel_size'])) {
                return false;
            }
            if (!is_numeric($item['units']) || !is_numeric($item['kernel_size'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be array with elements in this format: {"units":1024,"activation":"relu","kernel_size":2}';
    }
}
