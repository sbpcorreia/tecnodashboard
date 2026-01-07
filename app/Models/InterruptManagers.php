<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterruptManagers extends Model
{
    protected $table = 'U_TABPRRESP';

    protected $primaryKey = 'u_tabprrespstamp';

    public $incrementing = false;

    protected $keyType = 'string';
}
