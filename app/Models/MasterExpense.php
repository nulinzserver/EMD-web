<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterExpense extends Model
{
    protected $table = 'tender_expense';

    protected $fillable = [
        'mc_id',
        't_id',
        'expense_category',
        'amount',
    ];
}
