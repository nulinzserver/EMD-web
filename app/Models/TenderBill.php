<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderBill extends Model
{
    protected $table = 'tender_bill';

    protected $fillable = [
        'mc_id',
        't_id',
        'payment_type',
        'work_done_amount',
        'taxable_amount',
        'it_amount',
        'cgst_amount',
        'sgst_amount',
        'lwf_amount',
        'others_amount',
        'withheld_amount',
        'collection_proof',
        'remarks',
        'total_amount'
    ];
    
    public function bill_collect()
    {
        return $this->hasOne(CollectBillAmount::class, 'bill_id', 'id');
    }
}
