<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //

    protected $fillable = [
        'user_id',
        'loan_id',
        'book_id',
        'payment_type',
        'transaction_id',
        'payment_method',
        'amount_paid',
        'status',
        'paid_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function contribution(){
        return $this->belongsTo(Contribution::class);
    }

    public function loan(){
        return $this->belongsTo(Loan::class);
    }
}
