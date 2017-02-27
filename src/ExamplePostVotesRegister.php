<?php

namespace Tmd\LaravelRegisters;

use DB;
use Exception;
use Tmd\LaravelRegisters\Base\AbstractValueRegister;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * An example use of a a register that stores additional data for the objects on it.
 * The user has voted a certain way on a post (e.g. upvoted or downvoted).
 */
class ExamplePostVotesRegister extends AbstractValueRegister
{
    protected function load()
    {
        $rows = DB::select(
            'SELECT userId, vote FROM post_votes WHERE postId = ?',
            [
                $this->owner->getKey(),
            ]
        );

        return $this->buildObjectsArrayFromLoadedData($rows, 'userId', 'vote');
    }

    protected function create(EloquentModel $object, array $data = [])
    {
        if (empty($data['vote'])) {
            throw new Exception("Vote is required.");
        }

        // Inserts into the post_likes table. Updates the saved value if it already exists in the table.
        $affectedRows = DB::affectingStatement(
            'INSERT INTO post_votes (userId, postId, vote) VALUES(?, ?, ?) 
             ON DUPLICAE KEY UPDATE vote = VALUES(vote)',
            [
                $object->getKey(),
                $this->owner->getKey(),
                $data['vote'],
            ]
        );

        return $affectedRows;
    }

    protected function destroy(EloquentModel $object)
    {
        $affectedRows = DB::affectingStatement(
            "DELETE FROM post_votes WHERE userId = ? AND postId = ?",
            [
                $object->getKey(),
                $this->owner->getKey(),
            ]
        );

        return $affectedRows;
    }
}
