<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'target_group',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
