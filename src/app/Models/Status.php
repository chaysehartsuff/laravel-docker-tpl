<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = ['user_id', 'code', 'meta_id', 'content'];

    /**
     * Set or update a status for a user.
     *
     * @param string $content
     * @param string $meta_id
     * @param string $code
     * @param int $user_id
     * @return Status
     */
    public static function set($code, $content, $meta_id, $user_id)
    {
        $status = self::updateOrCreate(
            ['user_id' => $user_id, 'meta_id' => $meta_id, 'code' => $code],
            ['content' => $content]
        );

        return $status;
    }

    /**
     * Remove a status for a user.
     *
     * @param string $meta_id
     * @param string $code
     * @param int $user_id
     * @return bool
     */
    public static function remove($meta_id, $code, $user_id)
    {
        return self::where('user_id', $user_id)
                   ->where('meta_id', $meta_id)
                   ->where('code', $code)
                   ->delete();
    }
}