<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterruptManagers extends Model
{
    protected $table = 'U_TABPRRESP';

    protected $primaryKey = 'u_tabprrespstamp';

    public $incrementing = false;

    protected $keyType = 'string';

    public function joinInterruptReasons() : BelongsTo {
        return $this->belongsTo(InterruptReasons::class, "tabprstamp", "u_tabprstamp");
    }


}
