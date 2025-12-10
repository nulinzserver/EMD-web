<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class MasterClient extends Model implements \Illuminate\Contracts\Auth\Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    protected $table = 'master_clients';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'phone_number',
        'password',
    ];

    public function tenders()
    {
        return $this->hasMany(MasterTender::class, 'mc_id', 'id');
    }

    public function expense()
    {
        return $this->hasMany(MasterExpense::class, 'mc_id', 'id');
    }

    public function user()
    {
        return $this->hasMany(AddUser::class, 'mc_id', 'id');
    }
}
