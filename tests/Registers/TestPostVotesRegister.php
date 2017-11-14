<?php

namespace Tmd\LaravelRegisters\Tests\Registers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Tmd\LaravelRegisters\Base\AbstractValueRegister;
use Tmd\LaravelRegisters\Exceptions\MissingValueException;
use Tmd\LaravelRegisters\Tests\Models\Post;
use Tmd\LaravelRegisters\Tests\Registers\Traits\TestableRegisterTrait;

/**
 * An example use of a a register that stores additional data for the objects on it.
 * The user has voted a certain way on a post (e.g. upvoted or downvoted).
 */
class TestPostVotesRegister extends AbstractValueRegister
{
    use TestableRegisterTrait;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    protected function getOwnerKey()
    {
        return $this->post->getKey();
    }

    protected function load(): array
    {
        $rows = DB::select(
            'SELECT userId, vote FROM post_votes WHERE postId = ?',
            [
                $this->getOwnerKey(),
            ]
        );

        return $this->buildObjectsArrayFromLoadedData($rows, 'userId', 'vote');
    }

    protected function create(Model $object, array $data = []): int
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

    protected function destroy(Model $object): int
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
