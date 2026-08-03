<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Contribution extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'book_id',
        'week_number',
        'contribution',
        'welfare',
        'penalty',
        'is_missed',
        'tenant_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function ledger()
    {
        return $this->hasOne(Ledger::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    
}
