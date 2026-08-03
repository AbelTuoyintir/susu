<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Loan extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'book_id',
        'amount',
        'interest',
        'due_date',
        'status',
        'tenant_id',
    ];

    protected $appends = ['amount_repaid', 'progress_percentage'];

    public function getAmountRepaidAttribute()
    {
        return $this->payments()->sum('amount_paid');
    }

    public function getProgressPercentageAttribute()
    {
        $totalOwed = $this->amount + $this->interest;
        if ($totalOwed <= 0) return 0;
        return round(($this->amount_repaid / $totalOwed) * 100);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function payments(){
        return $this->hasMany(LoanPayment::class);
    }

    public function ledger(){
        return $this->hasOne(Ledger::class);
    }
}
