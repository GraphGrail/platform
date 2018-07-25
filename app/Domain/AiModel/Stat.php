<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\AiModel;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Stat
 * @package App\Domain\AiModel
 * @property integer id
 * @property integer model_id
 * @property integer user_id
 * @property string query
 * @property string result
 */
class Stat extends Model
{
    protected $table = 'ai_model_stats';
    protected $fillable = ['user_id', 'model_id', 'query', 'result'];
}
