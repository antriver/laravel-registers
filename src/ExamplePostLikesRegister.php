<?php

namespace Tmd\LaravelRegisters;

use DB;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRegisters\Base\AbstractBooleanRegister;

/**
 * An example use of a a register that stores on/off values.
 * The user has either liked the post or they have not.
 */
class ExamplePostLikesRegister extends AbstractBooleanRegister
{
    protected function load()
    {
        $rows = DB::select(
            'SELECT userId FROM post_likes WHERE postId = ?',
            [
                $this->owner->getKey(),
            ]
        );

        return $this->buildObjectsArrayFromLoadedData($rows, 'userId');
    }

    protected function create(EloquentModel $object, array $data = [])
    {
        // Inserts into the post_likes table. Does nothing if it already exists in the table.
        $affectedRows = DB::affectingStatement(
            'INSERT INTO post_likes (userId, postId) VALUES(?, ?) ON DUPLICAE KEY UPDATE userId = VALUES(userId)',
            [
                $object->getKey(),
                $this->owner->getKey(),
            ]
        );

        if ($affectedRows) {
            // You can perform some additional calculations here. Like updating the like count on the post object.
            ++$this->owner->likeCount;
            $this->owner->save();

            // Maybe fire an event or two.
            event(new NewPostLikeEvent($this->owner, $object));
        }

        return $affectedRows;
    }

    protected function destroy(EloquentModel $object)
    {
        $affectedRows = DB::affectingStatement(
            "DELETE FROM post_likes WHERE userId = ? AND postId = ?",
            [
                $object->getKey(),
                $this->owner->getKey(),
            ]
        );

        if ($affectedRows) {
            // You can perform some additional calculations here. Like updating the like count on the post object.
            $this->owner->likeCount -= $affectedRows;
            $this->owner->save();
        }

        return $affectedRows;
    }
}
