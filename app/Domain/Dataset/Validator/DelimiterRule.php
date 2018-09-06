<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset\Validator;


use App\Domain\Dataset\Dataset;
use Illuminate\Contracts\Validation\Rule;

class DelimiterRule implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $dataset
     * @return bool
     */
    public function passes($attribute, $dataset)
    {
        if (!$dataset instanceof Dataset) {
            return false;
        }
        $storage = new \App\Domain\Dataset\Storage();

        if (!$storage->fileExists($dataset)) {
            return false;
        }
        $path = $storage->getPath($dataset);

        if (!$handle = fopen($path, 'rb')) {
            return false;
        }
        $checkLines = 1;

        while ($checkLines && ($line = fgets($handle)) !== false) {
            if (false === strpos($line, ',')) {
                fclose($handle);
                return false;
            }

            $checkLines--;
        }
        fclose($handle);

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Dataset must have delimiter as ","';
    }
}
