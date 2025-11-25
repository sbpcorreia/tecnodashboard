<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkCenters extends Model
{
    protected $table = "u_tabct";

    protected $primaryKey = 'u_tabctstamp';

    public $incrementing = false;

    protected $keyType = 'string';

    public function scopeWorkCenterStopped($query) {
        return $query->whereRay("(SELECT TOP 1 tipo FROM u_logprod WHERE u_logprod.tabctstamp=u_tabctstamp ORDER BY ousrdata DESC, ousrhora DESC)=2");
    }

    public function lastLog() : HasOne {
        return $this->hasOne(ProductionLog::class, "tabctstamp", "u_tabctstamp")
        ->ofMany(['ousrdata' => 'max', 'ousrhora' => 'max', 'u_logprodstamp' => 'max'])
        ->latestOfMany(['ousrdata', 'ousrhora']);
    }

    public function scopeOnlyActives($query) {
        return $query->where("inactivo", 0)->where("noonline", 0);
    }
}
