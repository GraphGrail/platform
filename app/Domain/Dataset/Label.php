<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Label
 * @package App\Domain\Dataset
 * @property integer id
 * @property integer parent_id
 * @property string text
 */
class Label extends Model
{

    protected $fillable = ['text', 'parent_id', 'label_group_id'];
}
