<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUniqueStringIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TouchLog extends Model
{
    use HasUniqueStringIds;

    protected $table = "u_logtouch";

    protected $primaryKey = 'u_logtouchstamp';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;



    protected $fillable = [
        'datareg',
        'horareg',
        'posto',
        'quant',
        'tabofopstamp',
        'numof',
        'codct',
        'pestamp',
        'tipo',
        'etiquetastamp',
        'bistamp',
        'seiki',
        'tabprstamp',
        'lote',
        'caixa',
        'ousrinis',
        'ousrdata',
        'ousrhora',
        'usrinis',
        'usrdata',
        'usrhora',
        'responsavel'
    ];

    public function joinWorkCenter() {
        return $this->belongsTo(WorkCenters::class, "codct", "codct");
    }

    public function joinWorkOrderOperations() {
        return $this->belongsTo(WorkOrderOperations::class, "tabofopstamp", "u_tabofopstamp");
    }

    public function joinInterruptReasons() {
        return $this->belongsTo(InterruptReasons::class, "tabprstamp", "u_tabprstamp");
    }

    public function scopeOnlyInterruptedOperations($query) {
        return $query->whereIn('tipo', [2]);
    }



    public function scopeOnlyOpenOperations($query) {
        return $query->join(WorkOrderOperations::class, function($join) {
            $join->on("u_tabofop.u_tabofopstamp", "=", "u_logtouch.tabofopstamp")
                ->where("u_tabop.idto", "<>", 5);
        })
        ->join(WorkOrders::class, function($join) {
            $join->on("u_tabof.u_tabofstamp", "=", "u_tabofop.u_tabofstamp")
                ->where("u_tabof.idto", "<>", 5);
        })->select([
            'u_logtouch.*',
            'u_tabof.numof',
            'u_tabof.u_tabofstamp',
            'u_tabofop.numop',
            'u_tabofop.descop'
        ]);
    }

    public function newUniqueId() : string
    {
        return 'WEB' . date('YmdHis') . '.' . substr(Str::uuid(), 0, 11);
    }

    public function isValidUniqueId($value): bool
    {
        return true;
    }

    public static function newId() {
        return date('ymdHis') . '-' . substr(Str::uuid(), 0, 11);
    }

}
