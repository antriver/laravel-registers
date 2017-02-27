# Laravel Registers

This package provides 'registers', which are simple lists of models 'belonging to' another model. For example, a list of `User`s who have liked a `Post`. Or a list of `Achievement`s a `User` has earnt. The registers also take care of caching what items are on the list.

## Terminology

The `owner` of a register is the model the list belongs to. In the case of a 'post likers' register, this would be the `Post` model.

The `object` is what you add to the register / check is on the register / delete from the register. In the case of a 'post likers' register, this would be the `User` model.

## Installation
```
composer require tmd/laravel-registers
```

## Integration

Create a class that extends either `AbstractBooleanRegister` or `AbstractValueRegister`.

### `AbstractBooleanRegister`
This type of register serves as a simple 'is it here or not' list. Example use: Users who have liked a Post.

### `AbstractValueRegister`
This type of register allows the same setting/checking as `AbstractBooleanRegister` but you can also set some data. Examples use: Users who have voted on a Post. The additional data would be the user's vote.

The only difference between the two is `AbstractBooleanRegister`'s `check()` method will return a boolean. But `AbstractValueRegister` will return whatever data you stored about the entry.

In both cases you must implement these methods:

### `create($object, array $data = [])`
This should add an entry to the database to permanently store the action that has been performed (e.g. INSERT an entry in the post_likes or post_votes table). This should return an integer to show the result.
* 1 = Object was added to the register.
* 2 = Object was already on the register and so was modified. 
* 0 = Object was already on the register but the value was the same, so was not modified.

The easiest way to get these return values is to use a unique key on the 2 IDs on your database table (e.g. a unique key on postId + userId for a post likers register.) Then the query you run in `create()` should use some form of `ON DUPLICATE KEY UPDATE` and return the number of rows affected.

Hints:

The post will be accessible as `$this-owner`, the user will be the passed in `$object`.

### `destroy($object)`
This should delete the row in the database relating to the action (e.g. DELETE the entry from the post_likes table) and return the number of rows affected. If this returns 0 an exception will be thrown, as that meant the object was not on the register.

### `load()`
This should return all of the items on the register (e.g. SELECT all the entries about this post from the post_likes table).
This method should return an associative array where the keys are the IDs of the objects and the value is information about that object's entry.

For `AbstractValueRegister` this is straightforward - return whatever value you stored about the entry (e.g. which way the user voted, in the case of a post voters register.) 

For `AbstractBooleanRegister` this may make less sense, but you can return anything as the value. It's recommended to just use something like `true`. The reason the array is this way round is that it can be much faster to use isset() (cheking against the key) than in_array() (checking against the value) on larger arrays ([see http://maettig.com/1397246220]([http://maettig.com/1397246220)).

You can use the `buildObjectsArrayFromLoadedData()` helper method to provide the return value.

## Examples

See [`ExamplePostLikesRegister.php`](tests/Repositories/ExamplePostLikesRegister.php) and [`ExamplePostVotesRegister.php`](tests/Repositories/ExamplePostVotesRegister.php) for example implementations.

## Usage
Both `AbstractBooleanRegister` and `AbstractValueRegister` provide the same public methods.

### `add($object, array $data = [])`
Add the object to the register. Ignore the `$data` property if using the `AbstractBoolenRegister`.

### `remove($object)`
Remove the object from the register.

### `check($object)`
For `AbstractBooleanRegister`, returns `true` or `false` if the object is on the register.

For `AbstractValueRegister`, returns the stored data if the object is on the register, otherwise returns `null`.

### `all()`
Returns all the objects on the register.

### `keys()`
Returns the primary keys of all the objects on the register.

### `count()`
Returns the number of objects on the register.

### `refresh()`
Updates the cache of objects on the register.
