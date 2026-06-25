<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    //
    protected $fillable = [
        'user_id',
        'book_number',
        'contribution_amount',
        'duration_weeks',
        'start_date',
        'end_date',
        'status',
    ];

    protected $appends = ['total_contributions', 'balance'];

    public function getTotalContributionsAttribute()
    {
        return $this->ledgers()->where('type', 'contribution')->sum('amount');
    }

    public function getBalanceAttribute()
    {
        $contributions = $this->ledgers()->where('type', 'contribution')->sum('amount');
        $loans = $this->loans()->sum('amount');
        return $contributions - $loans;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
