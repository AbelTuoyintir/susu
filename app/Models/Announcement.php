<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Announcement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'title',
        'content',
        'type',
        'target_group',
        'user_id',
        'tenant_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
