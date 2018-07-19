<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Configuration;

use Illuminate\Database\Eloquent\Model;


/**
 * Class ComponentRelation
 * @package App\Domain\Configuration
 * @property integer configuration_id
 * @property string component_class
 * @property array component_attributes
 */
class ComponentRelation extends Model
{
    protected $casts = [
        'component_attributes' => 'array',
    ];
}
