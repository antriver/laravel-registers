<?php

namespace Tmd\LaravelRegisters\Tests;

use Cache;
use Orchestra\Testbench\TestCase;
use Tmd\LaravelRegisters\Exceptions\AlreadyOnRegisterException;
use Tmd\LaravelRegisters\Exceptions\MissingValueException;
use Tmd\LaravelRegisters\Exceptions\NotOnRegisterException;
use Tmd\LaravelRegisters\Tests\Models\Post;
use Tmd\LaravelRegisters\Tests\Models\User;
use Tmd\LaravelRegisters\Tests\Registers\TestPostLikesRegister;
use Tmd\LaravelRegisters\Tests\Registers\TestPostVotesRegister;

class AbstractBooleanRepositoryTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'register-tests');
        $app['config']->set(
            'database.connections.register-tests',
            [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'register-tests',
                'username' => 'root',
                'password' => 'root',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ]
        );

        $app['config']->set('cache.default', 'array');
        $app['config']->set(
            'cache.stores',
            [
                'array' => [
                    'driver' => 'array',
                ],
            ]
        );
    }

    public function tearDown()
    {
        \DB::delete("DELETE FROM post_likes");
        \DB::delete("DELETE FROM post_votes");
        Cache::flush();
    }

    protected function createPost()
    {
        return new Post(['id' => 50]);
    }

    protected function createUser()
    {
        return new User(['id' => 99]);
    }

    protected function createBooleanRegister()
    {
        return new TestPostLikesRegister($this->createPost());
    }

    protected function createValueRegister()
    {
        return new TestPostVotesRegister($this->createPost());
    }

    public function testAddThenRemoveObject()
    {
        $register = $this->createBooleanRegister();
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

    public function testAddDuplicateObjectThrowsException()
    {
        $register = $this->createBooleanRegister();
        $user = $this->createUser();

        $register->add($user);

        $this->expectException(AlreadyOnRegisterException::class);

        $register->add($user);
    }

    public function testRemoveObjectThrowsException()
    {
        $register = $this->createBooleanRegister();
        $user = $this->createUser();

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());

        $this->expectException(NotOnRegisterException::class);

        $register->remove($user);
    }

    public function testObjectsAreStoredInCache()
    {
        $register = $this->createBooleanRegister();
        $user = $this->createUser();

        $register->add($user);

        $this->assertSame(
            [
                99 => true
            ],
            Cache::get('testpostlikesregister:50')
        );
    }

    public function testCacheIsCleared()
    {
        $register = $this->createBooleanRegister();
        $user = $this->createUser();

        $register->add($user);

        $this->assertSame(
            [
                99 => true
            ],
            Cache::get('testpostlikesregister:50')
        );

        $register->remove($user);

        $this->assertSame(
            [],
            Cache::get('testpostlikesregister:50')
        );
    }

    public function testCachedCopyIsUsed()
    {
        $register = $this->createBooleanRegister();
        $user = $this->createUser();

        $this->assertNull(
            Cache::get('testpostlikesregister:50')
        );

        $this->assertEmpty($register->all());
        $this->assertFalse($register->check($user));
        $this->assertSame(0, $register->count());

        $this->assertInternalType(
            'array',
            Cache::get('testpostlikesregister:50')
        );

        $this->assertSame(
            [],
            Cache::get('testpostlikesregister:50')
        );

        // Set something else in the cached array and ensure that is what is returned.
        Cache::put('testpostlikesregister:50', [123 => true], 1);

        // Create a new register so the $object property cache is not used.
        unset($register);
        $register2 = $this->createBooleanRegister();

        $this->assertSame(
            [
                123 => true
            ],
            $register2->all()
        );

        $this->assertSame(1, $register2->count());
    }

    public function testExceptionIsThrownWithNoValue()
    {
        $register = $this->createValueRegister();
        $user = $this->createUser();

        $this->expectException(MissingValueException::class);
        $register->add($user, []);

    }

    public function testAddThenRemoveValueObject()
    {
        $register = $this->createValueRegister();
        $user = $this->createUser();

        $this->assertEmpty($register->all());
        $this->assertNull($register->check($user));
        $this->assertSame(0, $register->count());
        $this->assertSame([], $register->keys());

        $register->add($user, ['vote' => 'up']);

        $this->assertSame(
            [
                99 => 'up'
            ],
            $register->all()
        );
        $this->assertSame('up', $register->check($user));
        $this->assertSame(1, $register->count());
        $this->assertSame([99], $register->keys());

        $register->remove($user);

        $this->assertEmpty($register->all());
        $this->assertNull($register->check($user));
        $this->assertSame(0, $register->count());
        $this->assertSame([], $register->keys());
    }

}
