<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTender extends Model
{
    protected $table = 'master_tender';

    public function tender_profile()
    {
        return $this->hasOne(MasterClient::class, 'id', 'mc_id');
    }

    public function tender_bill()
    {
        return $this->hasMany(TenderBill::class, 't_id', 'id');
    }

    public function te_status()
    {
        return $this->hasMany(TenderStatus::class, 't_id');
    }

    public function collected_bill_amounts()
    {
        return $this->hasManyThrough(
            CollectBillAmount::class,
            TenderBill::class,
            't_id',
            'bill_id',
            'id',
            'id'
        );
    }
}
