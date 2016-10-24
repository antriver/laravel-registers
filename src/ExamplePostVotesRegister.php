<?php

namespace Tmd\LaravelRegisters;

use DB;
use Exception;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRegisters\Base\AbstractValueRegister;

/**
 * An example use of a a register that stores additional data for the objects on it.
 * The user has voted a certain way on a post (e.g. upvoted or downvoted)
 */
class ExamplePostVotesRegister extends AbstractValueRegister
{
    /**
     * @var EloquentModel
     */
    protected $post;

    /**
     * @param EloquentModel $post
     */
    public function __construct(EloquentModel $post)
    {
        $this->post = $post;
    }

    /**
     * Return a string to be the key for caching which objects are on this register.
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return 'post-votes-'.$this->post->getKey();
    }

    /**
     * This returns the information that will be cached by $this->all().
     * Actually check the database to return an array of object keys that have performed the action (uncached).
     * This should return an array where THE ARRAY KEYS ARE THE OBJECT KEYS. There can be some arbitrary small value
     * like true or 1 as the array values.
     * The reason for this is it's much faster to use isset() than in_array() on larger arrays.
     * See: http://maettig.com/1397246220
     *
     * @return array
     */
    protected function loadObjects()
    {
        $rows = DB::select(
            'SELECT userId, vote FROM post_votes WHERE postId = ?',
            [
                $this->post->getKey(),
            ]
        );

        return $this->buildObjectArrayFromCollection($rows, 'userId', 'vote');
    }

    /**
     * Create the underling database entry for the action.
     *
     * @param mixed $object
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    protected function create($object, array $data = [])
    {
        if (empty($data['vote'])) {
            throw new Exception("Vote is required.");
        }
        $vote = $data['vote'];

        $newVote = PostVote::create(
            [
                'postId' => $this->post->getKey(),
                'userId' => $object->getKey(),
                'vote' => $vote,
            ]
        );

        // You can perform some additional calculations here. Like updating the vote count on the post object.
        ++$this->post->votes;
        $this->post->save();

        // Maybe fire an event or two.
        //event(new NewPostVoteEvent($newVote));

        return $newVote;
    }

    /**
     * Delete the underling database entry for the action.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    protected function destroy($object)
    {
        $oldVotes = PostVote::where('postId', $this->post->getKey())->where('userId', $object->getKey())->get();

        $deleted = 0;
        foreach ($oldVotes as $oldVote) {
            --$this->post->votes;
            $oldVote->delete();
            ++$deleted;
        }

        return $deleted;
    }
}
