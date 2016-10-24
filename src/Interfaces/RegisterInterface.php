<?php

namespace Tmd\LaravelRegisters\Interfaces;

use Illuminate\Database\Eloquent\Model as EloquentModel;

interface RegisterInterface
{
    /**
     * Add the given object to the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param EloquentModel $object
     * @param array         $data Optional additional data to pass to the register.
     *
     * @return mixed
     */
    public function add(EloquentModel $object, array $data = []);

    /**
     * Remove the given object from the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    public function remove(EloquentModel $object);

    /**
     * Check if the given object is on the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    public function check(EloquentModel $object);

    /**
     * Return all the information about all of the objects on the register.
     *
     * @param bool $useCache Should a cached copy be used if available? Can be false to bypass the cache.
     *
     * @return array
     */
    public function all($useCache = true);

    /**
     * Return a single dimensional array of the keys of the objects on the register.
     *
     * @return array
     */
    public function keys();

    /**
     * Clear any cached data about the objects on the register.
     *
     * @return mixed
     */
    public function refresh();
}
