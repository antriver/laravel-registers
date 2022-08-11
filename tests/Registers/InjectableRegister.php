<?php

namespace Antriver\LaravelRegisters\Tests\Registers;

use Illuminate\Database\Eloquent\Model;
use Antriver\LaravelRegisters\Interfaces\RegisterInterface;
use Antriver\LaravelRegisters\Tests\Registers\Traits\TestableRegisterTrait;
use Antriver\LaravelRegisters\Traits\InjectableRegisterWrapperTrait;

class InjectableRegister implements RegisterInterface
{
    use InjectableRegisterWrapperTrait;
    use TestableRegisterTrait;

    public function __construct(Model $post = null)
    {
        if ($post) {
            $this->register = new TestPostLikesRegister($post);
        }
    }
}
