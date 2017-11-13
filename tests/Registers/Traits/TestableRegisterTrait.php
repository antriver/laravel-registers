<?php

namespace Tmd\LaravelRegisters\Tests\Registers\Traits;

use Illuminate\Database\Eloquent\Model;

trait TestableRegisterTrait
{
    public $onAddCalled = false;
    public $onAddClosure = null;
    public $onRemoveCalled = false;
    public $onRemoveClosure = null;

    protected function onAdd(Model $object)
    {
        $this->onAddCalled = true;

        if ($this->onAddClosure instanceof \Closure) {
            ($this->onAddClosure)($object);
        }
    }

    protected function onRemove(Model $object)
    {
        $this->onRemoveCalled = true;

        if ($this->onRemoveClosure instanceof \Closure) {
            ($this->onRemoveClosure)($object);
        }
    }
}
