<?php

namespace Tmd\LaravelRegisters\Base;

use Cache;
use Countable;
use Exception;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ReflectionClass;
use Tmd\LaravelRegisters\Exceptions\AlreadyOnRegisterException;
use Tmd\LaravelRegisters\Exceptions\NotOnRegisterException;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;

/**
 * A register is a simple list of models belonging to another model.
 * E.g. users a user follows, posts a user loves, or topics a post is in.
 * The "owner" of the register is who the objects belong to.
 * The "object" is the item being added to the register.
 */
abstract class AbstractRegister implements RegisterInterface, Countable
{
    /**
     * This 'owner' is the model the list belongs to. In the case of a 'post likes' register this should be the post.
     *
     * @var EloquentModel
     */
    protected $owner;

    /**
     * @var null|array
     */
    protected $objects = null;

    /**
     * Query the database to find the objects on the register.
     * This should return an array where the array keys are the primary keys of the objects. (See README for more info.)
     *
     * @return array
     */
    abstract protected function load();

    /**
     * Create the underling database entry for the action.
     * e.g. an entry in the post_likes table
     *
     * @param mixed $object
     * @param array $data
     *
     * @return int
     */
    abstract protected function create(EloquentModel $object, array $data = []);

    /**
     * Delete the underling database entry for the action.
     * e.g. an entry in the post_likes table
     *
     * @param mixed $object
     *
     * @return mixed
     */
    abstract protected function destroy(EloquentModel $object);

    /**
     * Add the given object to the register.
     *
     * @param mixed $object
     * @param array $data
     *
     * @return boolean
     * @throws Exception
     */
    public function add(EloquentModel $object, array $data = [])
    {
        if ($result = $this->create($object, $data)) {
            $this->refresh();

            $this->onAdd($object);

            return true;
        }

        throw $this->getAlreadyOnRegisterException($object);
    }

    /**
     * @param mixed $object
     *
     * @return mixed
     * @throws Exception
     */
    public function remove(EloquentModel $object)
    {
        if ($response = $this->destroy($object)) {
            $this->refresh();

            $this->onRemove($object);

            return true;
        }

        throw $this->getNotOnRegisterException($object);
    }

    /**
     * Check if the given object is on the register.
     *
     * @param EloquentModel $object
     *
     * @return bool
     */
    public function check(EloquentModel $object)
    {
        $objectKey = $this->getObjectKey($object);

        return $this->checkKey($objectKey);
    }

    /**
     * Returns all the information about all of the objects on the register.
     * Uses a cached copy if available.
     *
     * @return array
     */
    public function all()
    {
        return $this->getObjects(true);
    }

    /**
     * Updates the cache of objects on the register, and returns all the items.
     *
     * @return array
     */
    public function refresh()
    {
        return $this->getObjects(false);
    }

    /**
     * Return an array of the keys of objects on the register.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->all());
    }

    /**
     * Return the number of objects on the register.
     *
     * @return int
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * Returns information about all the objects on the register.
     * Gets from (in order of preference):
     * 1. In
     *
     * @param bool $useCache
     *
     * @return array|null
     */
    protected function getObjects($useCache = true)
    {
        // Use objects already in memory if available
        if ($useCache && is_array($this->objects)) {
            return $this->objects;
        }

        // Use objects in cache if available
        $cacheKey = $this->getCacheKey();
        if ($useCache && $cacheKey && !is_null($value = Cache::get($cacheKey))) {
            $this->objects = $value;

            return $value;
        }

        $this->objects = $this->load();

        if ($cacheKey) {
            Cache::forever($cacheKey, $this->objects);
        }

        return $this->objects;
    }

    /**
     * Return the primary key of the given object, for checking against the items on the regisrer.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    protected function getObjectKey($object)
    {
        return $object->getKey();
    }

    /**
     * Return the primary key of the owner of this register.
     *
     * @return mixed
     */
    protected function getOwnerKey()
    {
        return $this->owner->getKey();
    }

    /**
     * Returns a string to be the key for caching which objects are on this register.
     * Return null to disable use of Laravel's cache (will still use the in-memory cache).
     *
     * @return string|null
     */
    protected function getCacheKey()
    {
        $reflect = new ReflectionClass($this);
        return strtolower($reflect->getShortName()).':'.$this->getOwnerKey();
    }

    /**
     * Returns (not throws) the Exception to be thrown when trying to add an object already on the register.
     *
     * @param EloquentModel $object
     *
     * @return Exception
     */
    protected function getAlreadyOnRegisterException(EloquentModel $object)
    {
        $className = get_class($object);
        $objectKey = $this->getObjectKey($object);

        return new AlreadyOnRegisterException("{$className} {$objectKey} is already on the register.");
    }

    /**
     * Returns (not throws) the Exception to be thrown when trying to remove an object not on the register.
     *
     * @param EloquentModel $object
     *
     * @return Exception
     */
    protected function getNotOnRegisterException(EloquentModel $object)
    {
        $className = get_class($object);
        $objectKey = $this->getObjectKey($object);

        return new NotOnRegisterException("{$className} {$objectKey} is not on the register.");
    }

    /**
     * Takes an array of database results and returns an array where the specified column is used for the keys,
     * and the specified column is used for the values.
     *
     * @param object[]    $rows
     * @param string      $keyColumn
     * @param string|bool $valueColumn If a string is given, that column from each row will be used as its value.
     *                                 Otherwise, the literal value of $valueColumn will be used.
     *
     * @return array
     */
    protected function buildObjectsArrayFromLoadedData($rows, $keyColumn, $valueColumn = true)
    {
        $values = [];
        $fixedValue = !is_string($valueColumn);

        foreach ($rows as $item) {
            $key = $item->{$keyColumn};
            $values[$key] = $fixedValue ? $valueColumn : $item->{$valueColumn};
        }

        return $values;
    }

    /**
     * Called when an object is added to the register.
     *
     * @param EloquentModel $object
     */
    protected function onAdd(EloquentModel $object)
    {
        // Does nothing by default.
    }

    /**
     * Called when an object is removed from the register.
     *
     * @param EloquentModel $object
     */
    protected function onRemove(EloquentModel $object)
    {
        // Does nothing by default.
    }
}
