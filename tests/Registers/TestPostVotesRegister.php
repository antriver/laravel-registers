<?php

namespace Tmd\LaravelRegisters\Tests\Registers;

use DB;
use Tmd\LaravelRegisters\Base\AbstractValueRegister;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRegisters\Exceptions\MissingValueException;

/**
 * An example use of a a register that stores additional data for the objects on it.
 * The user has voted a certain way on a post (e.g. upvoted or downvoted).
 */
class TestPostVotesRegister extends AbstractValueRegister
{
    /**
     * @param EloquentModel $owner
     */
    public function __construct(EloquentModel $owner)
    {
        $this->owner = $owner;
    }

    protected function load()
    {
        $rows = DB::select(
            'SELECT userId, vote FROM post_votes WHERE postId = ?',
            [
                $this->getOwnerKey(),
            ]
        );

        return $this->buildObjectsArrayFromLoadedData($rows, 'userId', 'vote');
    }

    protected function create(EloquentModel $object, array $data = [])
    {
        if (empty($data['vote'])) {
            throw new MissingValueException("Vote is required.");
        }

        // Inserts into the post_likes table. Updates the saved value if it already exists in the table.
        $affectedRows = DB::affectingStatement(
            'INSERT INTO post_votes (userId, postId, vote) VALUES(?, ?, ?) 
             ON DUPLICATE KEY UPDATE vote = VALUES(vote)',
            [
                $this->getObjectKey($object),
                $this->getOwnerKey(),
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
                $this->getObjectKey($object),
                $this->getOwnerKey(),
            ]
        );

        return $affectedRows;
    }
}
