<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Dataset
 * @package App\Domain\Dataset
 * @property integer id
 * @property integer user_id
 * @property string name
 * @property string file
 * @property Data[] data
 * @property LabelGroup labelGroup
 */
class Dataset extends Model
{
    protected $fillable = ['user_id', 'file', 'name', 'label_group_id'];

    public function data()
    {
        return $this->hasMany(Data::class);
    }

    public function labelGroup()
    {
        return $this->belongsTo(LabelGroup::class);
    }
}
