<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Data
 * @package App\Domain\Dataset
 * @property integer id
 * @property integer dataset_id
 * @property integer label_id
 * @property string text
 */
class Data extends Model
{

    protected $fillable = ['dataset_id', 'label_id', 'text'];
}
