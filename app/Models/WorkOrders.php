<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrders extends Model
{
    protected $table = 'u_tabof';

    protected $primaryKey = 'u_tabofstamp';

    public $incrementing = false;

    protected $keyType = 'string';
}
