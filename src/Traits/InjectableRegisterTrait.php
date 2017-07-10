<?php

namespace Tmd\LaravelRegisters\Traits;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRegisters\Base\AbstractRegister;

/**
 * Provides a wrapper around a real register object. This can be used to
 * create a register that can be injected, but relies on something else being passed in the constructor
 * (such as the authenticated user). The constructor of the class using this trait should initialize and
 * set a register as $this->register.
 *
 * @package Tmd\LaravelRegisters\Traits
 */
trait InjectableRegisterTrait
{
    /**
     * @var AbstractRegister
     */
    protected $register;

    public function ready(): bool
    {
        return $this->register !== null;
    }

    public function add(EloquentModel $object, array $data = [])
    {
        if ($this->register) {
            return $this->register->add($object, $data);
        }

        return null;
    }

    public function remove(EloquentModel $object)
    {
        if ($this->register) {
            return $this->register->remove($object);
        }

        return null;
    }

    public function check(EloquentModel $object)
    {
        if ($this->register) {
            return $this->register->check($object);
        }

        return null;
    }

    public function checkKey($objectKey)
    {
        if ($this->register) {
            return $this->register->checkKey($objectKey);
        }

        return null;
    }

    public function all()
    {
        if ($this->register) {
            return $this->register->all();
        }

        return null;
    }

    public function keys()
    {
        if ($this->register) {
            return $this->register->keys();
        }

        return null;
    }

    public function count()
    {
        if ($this->register) {
            return $this->register->count();
        }

        return null;
    }

    public function refresh()
    {
        if ($this->register) {
            return $this->register->refresh();
        }

        return null;
    }
}
