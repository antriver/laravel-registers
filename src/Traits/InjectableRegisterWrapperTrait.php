<?php

namespace Tmd\LaravelRegisters\Traits;

use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRegisters\Exceptions\RegisterNotReadyException;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;

/**
 * Provides a wrapper around a real register object. This can be used to
 * create a register that can be injected, but relies on something else being passed in the constructor
 * (such as the authenticated user). The constructor of the class using this trait should create a register and
 * set it as the $this->register property if all the dependencies have been injected.
 */
trait InjectableRegisterWrapperTrait
{
    /**
     * @var RegisterInterface
     */
    protected $register;

    public function ready(): bool
    {
        return $this->register !== null;
    }

    public function add(Model $object, array $data = []): bool
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->add($object, $data);
    }

    public function remove(Model $object): bool
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->remove($object);
    }

    public function check(Model $object)
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->check($object);
    }

    public function checkKey($objectKey)
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->checkKey($objectKey);
    }

    public function all(): array
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->all();
    }

    public function refresh(): array
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->refresh();
    }

    public function keys(): array
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->keys();
    }

    public function count(): int
    {
        if (!$this->ready()) {
            throw $this->createNotReadyException();
        }

        return $this->register->count();
    }

    protected function createNotReadyException(): RegisterNotReadyException
    {
        return new RegisterNotReadyException(
            'The register '.get_class($this).' has not been instantiated.'
        );
    }
}
