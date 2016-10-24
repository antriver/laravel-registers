<?php

namespace Tmd\LaravelRegisters\Interfaces;

interface RegisterInterface
{
    /**
     * Add the given object to the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param mixed $object
     * @param array $data Optional additional data to pass to the register.
     *
     * @return mixed
     */
    public function add($object, array $data = []);

    /**
     * Remove the given object from the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function remove($object);

    /**
     * Check if the given object is on the register.
     * May return a boolean, or data about that entry, depending upon the implementation.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function check($object);

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
