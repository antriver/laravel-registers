<?php

namespace Tmd\LaravelRegisters\Tests;

use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;
use Tmd\LaravelRegisters\Tests\Registers\TestPostLikesRegister;

class AbstractBooleanRegisterTest extends RegisterTestCase
{
    protected $defaultData = [];

    protected $expectedStoredData = [99 => true];

    public function testAddThenRemoveObject()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());
        $this->assertSame([], $register->keys());

        $register->add($user);

        $this->assertSame(
            [
                99 => true,
            ],
            $register->all()
        );
        $this->assertTrue($register->check($user));
        $this->assertSame(1, $register->count());
        $this->assertSame([99], $register->keys());

        $register->remove($user);

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());
        $this->assertSame([], $register->keys());
    }

    protected function createRegister(Model $owner): RegisterInterface
    {
        return new TestPostLikesRegister($owner);
    }
}
