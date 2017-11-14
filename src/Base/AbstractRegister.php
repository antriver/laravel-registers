<?php

namespace Tmd\LaravelRegisters\Base;

use Cache;
use Countable;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;
use Tmd\LaravelRegisters\Exceptions\AlreadyOnRegisterException;
use Tmd\LaravelRegisters\Exceptions\NotOnRegisterException;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;

/**
 * A register is a simple list of models belonging to another model.
 * E.g. users a user follows, posts a user loves, or topics a post is in.
 * The "owner" of the register is who the objects belong to. Note that the owner property has been removed in favor
 * of setting your own property with a more understandable name ($user, $post, etc.) and implementing the
 * getOwnerKey() method yourself.
 * The "object" is the item being added to the register.
 */
abstract class AbstractRegister implements RegisterInterface, Countable
{
    /**
     * A cache of the loaded objects that are on the register.
     *
     * @var null|array
     */
    protected $objects = null;

    /**
     * Return the primary key of the owner of this register.
     *
     * @return mixed
     */
    abstract protected function getOwnerKey();

    /**
     * Query the database to find the objects on the register.
     * This should return an array where the array keys are the primary keys of the objects.
     * (See README for more info.)
     *
     * @return array
     */
    abstract protected function load(): array;

    /**
     * Create the underling database entry for the action.
     * e.g. Insert an entry in the post_likes table
     * Return the number of affected rows.
     *
     * @param Model $object
     * @param array $data
     *
     * @return int
     */
    abstract protected function create(Model $object, array $data = []): int;

    /**
     * Delete the underling database entry for the action.
     * e.g. Delete an entry from the post_likes table
     * Return the number of affected rows.
     *
     * @param Model $object
     *
     * @return int
     */
    abstract protected function destroy(Model $object): int;

    /**
     * Add the given Model to the register.
     *
     * @param Model $object
     * @param array $data Optional additional data to pass to the register (needed for ValueRegister).
     *
     * @return bool
     * @throws Exception
     */
    public function add(Model $object, array $data = []): bool
    {
        if ($this->beforeCreate($object) !== true) {
            return false;
        }

        if ($affectedRows = $this->create($object, $data)) {
            $this->refresh();
            $this->afterCreate($object, true, $data);

            return true;
        } else {
            $this->afterCreate($object, false, $data);
            throw $this->getAlreadyOnRegisterException($object);
        }
    }

    /**
     * Remove the given Model from the register.
     *
     * @param Model $object
     *
     * @return bool
     * @throws Exception
     */
    public function remove(Model $object): bool
    {
        if ($this->beforeDestroy($object) !== true) {
            return false;
        }

        if ($deletedRows = $this->destroy($object)) {
            $this->refresh();
            $this->afterDestroy($object, $deletedRows);

            return true;
        } else {
            $this->afterDestroy($object, false);

            throw $this->getNotOnRegisterException($object);
        }
    }

    /**
     * Remove all of the objects on the register.
     *
     * @return bool
     */
    public function removeAll(): bool
    {
        if ($deletedRows = $this->destroyAll()) {
            $this->refresh();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Check if the given Model is on the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param Model $object
     *
     * @return mixed
     */
    public function check(Model $object)
    {
        $objectKey = $this->getObjectKey($object);

        return $this->checkKey($objectKey);
    }

    /**
     * Return all the information about all of the objects on the register.
     * Uses a cached copy if available.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getObjects(true);
    }

    /**
     * Clear any cached data about the objects on the register.
     * Returns a fresh copy of information about all of the objects on the register (the same as all())
     *
     * @return array
     */
    public function refresh(): array
    {
        return $this->getObjects(false);
    }

    /**
     * Return a single dimensional array of the keys of the objects on the register.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    /**
     * Return the number of objects on the register.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Delete all of the underlying database entries for.
     *
     * @return int
     * @throws Exception
     */
    protected function destroyAll(): int
    {
        throw new Exception('destroyAll() is not available for '.get_class($this));
    }

    /**
     * Returns information about all the objects on the register.
     *
     * @param bool $useCache
     *
     * @return array
     */
    protected function getObjects($useCache = true): array
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
     * Return the primary key of the given object, for checking against the items on the register.
     *
     * @param Model $object
     *
     * @return mixed
     */
    protected function getObjectKey($object)
    {
        return $object->getKey();
    }

    /**
     * Returns a string to be the key for caching which objects are on this register.
     * Return null to disable use of Laravel's cache (will still use the in-memory cache).
     *
     * @return string|null
     */
    protected function getCacheKey(): ?string
    {
        $reflect = new ReflectionClass($this);

        return strtolower($reflect->getShortName()).':'.$this->getOwnerKey();
    }

    /**
     * Returns (not throws) the Exception to be thrown when trying to add an object already on the register.
     *
     * @param Model $object
     *
     * @return AlreadyOnRegisterException
     */
    protected function getAlreadyOnRegisterException(Model $object): AlreadyOnRegisterException
    {
        $className = get_class($object);
        $objectKey = $this->getObjectKey($object);

        return new AlreadyOnRegisterException("{$className} {$objectKey} is already on the register.");
    }

    /**
     * Returns (not throws) the Exception to be thrown when trying to remove an object not on the register.
     *
     * @param Model $object
     *
     * @return NotOnRegisterException
     */
    protected function getNotOnRegisterException(Model $object): NotOnRegisterException
    {
        $className = get_class($object);
        $objectKey = $this->getObjectKey($object);

        return new NotOnRegisterException("{$className} {$objectKey} is not on the register.");
    }

    /**
     * Takes an array of database results and returns an array where the specified column is used for the keys,
     * and the specified column is used for the values.
     *
     * @param object[]|Collection $rows
     * @param string $keyColumn
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
     * This method is called before any modification is done by the add() method.
     * If it returns false add will be aborted.
     *
     * @param Model $object
     *
     * @return bool
     */
    protected function beforeCreate(Model $object): bool
    {
        return true;
    }

    /**
     * This method is called after any modification is finished by the add() method.
     * It is called even if they do not succeed.
     * If the add did not succeed an Exception will be thrown by add(). This is called before the exception is thrown
     * to allow for any cleanup to run (releasing locks maybe?)
     *
     * @param Model $object
     * @param bool $success
     * @param array $data
     */
    protected function afterCreate(Model $object, bool $success, array $data = [, $data])
    {
        // Backward-compatibility.
        if ($success && method_exists($this, 'onAdd')) {
            $this->onAdd($object);
        }
    }

    /**
     * This method is called before any modification is done by the remove() method.
     * If it returns false the removal will be aborted.
     *
     * @param Model $object
     *
     * @return bool
     */
    protected function beforeDestroy(Model $object): bool
    {
        return true;
    }

    /**
     * This method is called after any modification is finished by the remove() method.
     * It is called even if they do not succeed.
     * If the remove did not succeed an Exception will be thrown by remove(). This is called before the exception is
     * thrown to allow for any cleanup to run (releasing locks maybe?)
     *
     * @param Model $object
     * @param int $deletedRows The number of rows that were deleted (may be 0 to indicate a failure).
     */
    protected function afterDestroy(Model $object, int $deletedRows = 0)
    {
        // Backward-compatibility.
        if ($deletedRows > 0 && method_exists($this, 'onRemove')) {
            $this->onRemove($object);
        }
    }
}
