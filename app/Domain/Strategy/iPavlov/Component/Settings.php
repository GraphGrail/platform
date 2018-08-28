<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Strategy\iPavlov\Component;


use App\Domain\Strategy\Component\Form\FieldForm;
use Illuminate\Validation\ValidationException;

class Settings extends Component
{

    protected $attributes = ['epochs'];

    public static function name(): string
    {
        return __('Settings');
    }

    public function description(): string
    {
        return __('Main settings');
    }

    public function validate($data): bool
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make($data, [
            'epochs' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->passes();
    }

    /**
     * @return FieldForm[]
     */
    public function getFields(): array
    {
        return (new \App\Domain\Strategy\iPavlov\Component\Form\Settings($this))->getFieldsFormObjects();
    }

    public function jsonSerialize()
    {
        return ['epochs' => (float)$this->attributes['epochs']];
    }
}
