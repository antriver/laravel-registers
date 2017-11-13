<?php

namespace Tmd\LaravelRegisters\Tests\Registers;

use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;
use Tmd\LaravelRegisters\Tests\Registers\Traits\TestableRegisterTrait;
use Tmd\LaravelRegisters\Traits\InjectableRegisterWrapperTrait;

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
