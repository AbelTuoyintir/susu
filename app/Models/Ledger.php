<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    //

    protected $fillable = [
        'user_id',
        'book_id',
        'type',
        'amount',
        'week_number',
        'description',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function contribution(){
        return $this->hasOne(Contribution::class);
    }

    public function loan(){
        return $this->hasOne(Loan::class);
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }
}
