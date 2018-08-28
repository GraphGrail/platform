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
 * @property integer status
 * @property string name
 * @property string file
 * @property Data[] data
 * @property LabelGroup labelGroup
 */
class Dataset extends Model
{
    public const STATUS_NEW = 100;
    public const STATUS_FILLING = 200;
    public const STATUS_READY = 300;

    protected $fillable = ['user_id', 'file', 'name', 'label_group_id', 'status'];

    public function data()
    {
        return $this->hasMany(Data::class);
    }

    public function labelGroup()
    {
        return $this->belongsTo(LabelGroup::class);
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function statusLabel(): string
    {
        return array_key_exists($this->status, $this->statuses()) ? $this->statuses()[$this->status] : '';
    }

    public function statuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_FILLING => 'Filling',
            self::STATUS_READY => 'Ready',
        ];
    }
}
