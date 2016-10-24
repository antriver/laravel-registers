<?php

namespace Tmd\LaravelRegisters\Base;

use Cache;
use Exception;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;

/**
 * A register is a simple list of models belonging to another model.
 * E.g. users a user follows or posts a user loves.
 *
 * The "owner", or "subject" of the register is who the objects belong to.
 * The "object" is the item being added to the register.
 */
abstract class AbstractRegister implements RegisterInterface
{
    /**
     * @var null|array
     */
    protected $objects = null;

    /**
     * Return a string to be the key for caching which objects are on this register.
     *
     * @return string
     */
    abstract protected function getCacheKey();

    /**
     * This returns the information that will be cached by $this->all().
     * Actually check the database to return an array of object keys that have performed the action (uncached).
     * This should return an array where THE ARRAY KEYS ARE THE OBJECT KEYS. There can be some arbitrary small value
     * like true or 1 as the array values.
     * The reason for this is it's much faster to use isset() than in_array() on larger arrays.
     * See: http://maettig.com/1397246220
     *
     * @return array
     */
    abstract protected function loadObjects();

    /**
     * Create the underling database entry for the action.
     *
     * @param mixed $object
     * @param array $data
     *
     * @return mixed
     */
    abstract protected function create($object, array $data = []);

    /**
     * Delete the underling database entry for the action.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    abstract protected function destroy($object);

    /**
     * Return all the information about all of the objects on the register.
     * Uses a cached copy if available.
     *
     * @param bool $useCache Should a cached copy be used if available? Can be false to bypass the cache.
     *
     * @return array
     */
    public function all($useCache = true)
    {
        if ($useCache && is_array($this->objects)) {
            return $this->objects;
        }

        $cacheKey = $this->getCacheKey();
        if ($useCache && !is_null($value = Cache::get($cacheKey))) {
            $this->objects = $value;

            return $value;
        }

        $this->objects = $this->loadObjects();

        Cache::forever($cacheKey, $this->objects);

        return $this->objects;
    }

    /**
     * @inheritDoc
     */
    public function keys()
    {
        return array_keys($this->all());
    }

    /**
     * @inheritDoc
     */
    public function remove($object)
    {
        if (!$this->check($object)) {
            throw $this->getNotOnRegisterException($object);
        }

        $response = $this->destroy($object);

        $this->refresh();

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function refresh()
    {
        $this->all(false);
    }

    /**
     * Get the key to use when storing an object on the register.
     * The default supports Eloquent models.
     *
     * @param mixed|EloquentModel $object
     *
     * @return mixed
     */
    protected function getObjectKey($object)
    {
        return $object->getKey();
    }

    /**
     * Returns (not throws) the Exception to be thrown when trying to add an object already on the register.
     *
     * @param mixed $object
     *
     * @return Exception
     */
    protected function getAlreadyOnRegisterException($object)
    {
        $className = get_class($object);
        $objectKey = $this->getObjectKey($object);

        return new Exception("{$className} {$objectKey} is already on the register.");
    }

    /**
     * Returns (not throws) the Exception to be thrown when trying to remove an object not on the register.
     *
     * @param mixed $object
     *
     * @return Exception
     */
    protected function getNotOnRegisterException($object)
    {
        $className = get_class($object);
        $objectKey = $this->getObjectKey($object);

        return new Exception("{$className} {$objectKey} is not on the register.");
    }

    /**
     * Helper used in the buildObjectArrayFromCollection method in subclasses.
     *
     * @param object       $item
     * @param string|array $column
     * @param string       $glue
     *
     * @return string
     */
    protected function buildStringFromItemValues($item, $column, $glue = '-')
    {
        if (is_array($column)) {
            $values = [];
            foreach ($column as $c) {
                $values[] = $item->{$c};
            }

            return implode($glue, $values);
        } else {
            return $item->{$key};
        }
    }
}
