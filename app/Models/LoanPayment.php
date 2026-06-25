<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    //
    protected $fillable = [
        'loan_id',
        'amount_paid',
    ];

    public function loan(){
        return $this->belongsTo(Loan::class);
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }
}
