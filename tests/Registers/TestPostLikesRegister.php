<?php

namespace Tmd\LaravelRegisters\Tests\Registers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRegisters\Base\AbstractBooleanRegister;
use Tmd\LaravelRegisters\Tests\Registers\Traits\TestableRegisterTrait;

/**
 * An example use of a a register that stores on/off values.
 * The user has either liked the post or they have not.
 */
class TestPostLikesRegister extends AbstractBooleanRegister
{
    use TestableRegisterTrait;

    /**
     * @param Model $owner
     */
    public function __construct(Model $owner)
    {
        $this->owner = $owner;
    }

    protected function load()
    {
        $rows = DB::select(
            'SELECT userId FROM post_likes WHERE postId = ?',
            [
                $this->getOwnerKey(),
            ]
        );

        return $this->buildObjectsArrayFromLoadedData($rows, 'userId');
    }

    protected function create(Model $object, array $data = []): int
    {
        // Inserts into the post_likes table. Does nothing if it already exists in the table.
        $affectedRows = DB::affectingStatement(
            'INSERT INTO post_likes (userId, postId) VALUES(?, ?) ON DUPLICATE KEY UPDATE userId = VALUES(userId)',
            [
                $this->getObjectKey($object),
                $this->getOwnerKey(),
            ]
        );

        if ($affectedRows) {
            // You can perform some additional calculations here. Like updating the like count on the post object.
            //++$this->owner->likeCount;
            //$this->owner->save();

            // Maybe fire an event or two.
            //event(new NewPostLikeEvent($this->owner, $object));
        }

        return $affectedRows;
    }

    protected function destroy(Model $object): int
    {
        $affectedRows = DB::affectingStatement(
            "DELETE FROM post_likes WHERE userId = ? AND postId = ?",
            [
                $this->getObjectKey($object),
                $this->getOwnerKey(),
            ]
        );

        if ($affectedRows) {
            // You can perform some additional calculations here. Like updating the like count on the post object.
            //$this->owner->likeCount -= $affectedRows;
            //$this->owner->save();
        }

        return $affectedRows;
    }
}
