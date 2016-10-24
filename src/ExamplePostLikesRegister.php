<?php

namespace Tmd\LaravelRegisters;

use DB;
use Exception;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tmd\LaravelRegisters\Base\AbstractBooleanRegister;

/**
 * An example use of a a register that stores on/off values.
 * The user has either liked the post or they havent.
 */
class ExamplePostLikesRegister extends AbstractBooleanRegister
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
        return 'post-likes-'.$this->post->getKey();
    }

    /**
     * @inheritDoc
     */
    protected function loadObjects()
    {
        $achievements = PostLike::where('postId', $this->post->getKey())->select('userId')->get();

        return $this->buildArrayFromCollection($achievements, 'userId');
    }

    /**
     * Create the underling database entry for the action.
     *
     * @param EloquentModel $object
     * @param array         $data
     *
     * @return mixed
     */
    protected function create(EloquentModel $object, array $data = [])
    {
        $postLike = PostLike::create(
            [
                'postId' => $this->post->getKey(),
                'userId' => $object->getKey(),
            ]
        );

        return $postLike;
    }

    /**
     * Delete the underling database entry for the action.
     *
     * @param EloquentModel $object
     *
     * @return mixed
     */
    protected function destroy(EloquentModel $object)
    {
        return DB::affectingStatement(
            "DELETE FROM post_likes WHERE userId = ? AND postId = ?",
            [
                $object->getKey(),
                $this->post->getKey(),
            ]
        );
    }
}
