<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LoanPayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'loan_id',
        'amount_paid',
        'tenant_id',
    ];

    public function loan(){
        return $this->belongsTo(Loan::class);
    }

    public function payment(){
        return $this->hasOne(Payment::class);
    }
}
