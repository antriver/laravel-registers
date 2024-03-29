<?php

namespace Antriver\LaravelRegisters\Tests;

use Illuminate\Database\Eloquent\Model;
use Antriver\LaravelRegisters\Exceptions\MissingValueException;
use Antriver\LaravelRegisters\Interfaces\RegisterInterface;
use Antriver\LaravelRegisters\Tests\Registers\TestPostVotesRegister;

class AbstractValueRegisterTest extends RegisterTestCase
{
    protected $defaultData = ['vote' => 'up'];

    protected $expectedStoredData = [99 => 'up'];

    public function testExceptionIsThrownWithNoValue()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $this->expectException(MissingValueException::class);
        $register->add($user, []);
    }

    public function testAddThenRemoveObject()
    {
        $post = $this->createPost();
        $register = $this->createRegister($post);
        $user = $this->createUser();

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());
        $this->assertSame([], $register->keys());

        $register->add($user, ['vote' => 'up']);

        $this->assertSame(
            [
                99 => 'up',
            ],
            $register->all()
        );
        $this->assertSame('up', $register->check($user));
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
        return new TestPostVotesRegister($owner);
    }
}
