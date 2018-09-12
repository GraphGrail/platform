<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset\Validator;


use App\Domain\Dataset\Dataset;
use Illuminate\Contracts\Validation\Rule;

class DelimiterRule implements Rule
{
    private $delimiter;

    public function __construct(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $dataset
     * @return bool
     */
    public function passes($attribute, $dataset): bool
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
            if (false === strpos($line, $this->delimiter)) {
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
    public function message(): string
    {
        return __('Dataset must have delimiter as ":delimiter"', ['delimiter' => $this->delimiter]);
    }
}
