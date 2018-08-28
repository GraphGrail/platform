<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain;


use App\Domain\Dataset\Dataset;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AiModel
 * @package App\Domain
 * @property integer id
 * @property integer status
 * @property array errors
 * @property integer user_id
 * @property float performance
 * @property Configuration configuration
 * @property Dataset dataset
 */
class AiModel extends Model
{
    public const STATUS_NEW                = 100;
    public const STATUS_VERIFYING_CONFIG   = 150;
    public const STATUS_VERIFY_CONFIG_OK   = 170;
    public const STATUS_VERIFY_CONFIG_FAIL = 180;
    public const STATUS_TRAINING           = 200;
    public const STATUS_TRAINED            = 300;
    public const STATUS_TESTING            = 400;
    public const STATUS_READY              = 500;
    public const STATUS_TEST_FAIL          = 9000;

    protected $fillable = ['user_id', 'status', 'dataset_id', 'configuration_id', 'performance'];

    public function configuration()
    {
        return $this->belongsTo(Configuration::class);
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function statusLabel(): string
    {
        return $this->statuses()[$this->status];
    }

    public function statuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_VERIFYING_CONFIG   => 'Config verifying',
            self::STATUS_VERIFY_CONFIG_OK   => 'Config verified',
            self::STATUS_VERIFY_CONFIG_FAIL => 'Config verification failed',
            self::STATUS_TRAINING => 'Training',
            self::STATUS_TRAINED => 'Trained',
            self::STATUS_TESTING => 'Testing',
            self::STATUS_READY => 'Ready',
            self::STATUS_TEST_FAIL => 'Failed',
        ];
    }

    public function setErrors($errors)
    {
        $this->errors = json_encode((array)$errors);
    }

    public function getErrors()
    {
        return (array)json_decode($this->errors, true);
    }
}
