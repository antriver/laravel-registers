<?php

namespace Tmd\LaravelRegisters\Tests;

use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase;
use Tmd\LaravelRegisters\Exceptions\RegisterNotReadyException;
use Tmd\LaravelRegisters\Interfaces\RegisterInterface;
use Tmd\LaravelRegisters\Tests\Registers\InjectableRegister;

class InjectableRegisterTest extends TestCase
{
    use RegisterTestSetupTrait;

    protected $defaultData = [];

    protected $expectedStoredData = [99 => true];

    protected function createRegister(Model $owner): RegisterInterface
    {
        return new InjectableRegister($owner);
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

        $register->add($user);

        $this->assertSame(
            [
                99 => true
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

    public function testNotReadyThrowsExceptionOnAdd()
    {
        $register = new InjectableRegister();

        $this->expectException(RegisterNotReadyException::class);
        $this->expectExceptionMessage(
            'The register Tmd\LaravelRegisters\Tests\Registers\InjectableRegister has not been instantiated.'
        );

        $user = $this->createUser();
        $register->add($user, $this->defaultData);
    }

    public function testNotReadyThrowsExceptionOnCheck()
    {
        $register = new InjectableRegister();

        $this->expectException(RegisterNotReadyException::class);
        $this->expectExceptionMessage(
            'The register Tmd\LaravelRegisters\Tests\Registers\InjectableRegister has not been instantiated.'
        );

        $user = $this->createUser();
        $register->check($user);
    }

    public function testNotReadyThrowsExceptionOnAll()
    {
        $register = new InjectableRegister();

        $this->expectException(RegisterNotReadyException::class);
        $this->expectExceptionMessage(
            'The register Tmd\LaravelRegisters\Tests\Registers\InjectableRegister has not been instantiated.'
        );

        $register->all();
    }
}
