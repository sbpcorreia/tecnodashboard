<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderOperations extends Model
{
    protected $table = 'u_tabofop';

    protected $primaryKey = 'u_tabofopstamp';

    public $incrementing = false;

    protected $keyType = 'string';

    public function joinWorkOrders() {
        return $this->belongsTo(WorkOrders::class, "tabofstamp", "u_tabofstamp");
    }
}
