<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain\Dataset;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Dataset
 * @package App\Domain\Dataset
 * @property integer id
 * @property integer user_id
 * @property integer status
 * @property bool system
 * @property string name
 * @property string lang
 * @property string file
 * @property Data[] data
 * @property LabelGroup labelGroup
 * @property boolean $exclude_first_row
 * @property string $delimiter
 */
class Dataset extends Model
{
    public const STATUS_NEW = 100;
    public const STATUS_FILLING = 200;
    public const STATUS_READY = 300;

    protected $fillable = ['user_id', 'file', 'name', 'lang', 'label_group_id', 'status', 'exclude_first_row', 'delimiter'];

    public function data(): HasMany
    {
        return $this->hasMany(Data::class);
    }

    public function labelGroup(): BelongsTo
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

    public function getFullName(): string
    {
        $name = $this->name;
        if ($this->lang) {
            $name .= '[' . $this->lang . ']';
        }

        return $name;
    }
}
