<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset;


use Illuminate\Database\Eloquent\Model;

/**
 * Class LabelGroup
 * @package App\Domain\Dataset
 * @property integer id
 * @property integer user_id
 * @property Label[] labels
 */
class LabelGroup extends Model
{
    protected $fillable = ['user_id'];

    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}
