<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Domain;


use App\Domain\Strategy\Component\Form\FieldForm;
use App\Domain\Strategy\Strategy;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use JsonSerializable;

/**
 * Class Component
 * @package App\Domain
 *
 * @property bool enabled
 * @property bool optional
 * @property int position
 */
abstract class Component implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    use HasAttributes;

    protected const CREATED_AT = 'created_at';

    protected const UPDATED_AT = 'updated_at';

    /** @var Strategy */
    protected $strategy;

    abstract public static function name(): string;
    abstract public function description(): string;
    abstract public function validate($data): bool;

    public function __construct(Strategy $strategy, $attributes = [])
    {
        $this->strategy = $strategy;

        $optional = false;
        if (\in_array('optional', $this->attributes, true)) {
            $optional = true;
        }
        if ($attributes) {
            $this->setRawAttributes($attributes);
        }
        $this->optional = $optional;
    }

    /**
     * @return FieldForm[]
     */
    abstract public function getFields(): array;

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->getAttribute($offset) !== null;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    public function buildConfig()
    {
        return $this->jsonSerialize();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return Strategy
     */
    public function getStrategy(): Strategy
    {
        return $this->strategy;
    }

    protected function getIncrementing()
    {
        return false;
    }

    protected function getVisible()
    {
        return [];
    }

    protected function getHidden()
    {
        return [];
    }

    protected function getArrayableRelations()
    {
        return [];
    }

    public function getRelationValue($key)
    {
        return null;
    }

    protected function usesTimestamps() {
        return false;
    }

    public function __toString()
    {
        return \get_class($this);
    }

    /**
     * @return array
     */
    protected function createParams(): array
    {
        $params = [];
        foreach ($this->attributes as $attribute) {
            if (null === $this->{$attribute}) {
                continue;
            }
            $params[$attribute] = $this->{$attribute};
        }
        return $params;
    }
}
