<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUniqueStringIds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductionLog extends Model
{

    use HasUniqueStringIds;

    protected $table = "u_logprod";

    protected $primaryKey = "u_logprodstamp";

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'data',
        'hora',
        'penome',
        'peno',
        'pestamp',
        'posto',
        'codct',
        'tabctstamp',
        'tabprstamp',
        'tipo',
        'tabofstamp',
        'tabofopstamp',
        'tabofopcostamp',
        'ref',
        'cor',
        'tam',
        'lote',
        'embalagem',
        'qtt',
        'custo',
        'tempo',
        'tratado',
        'numof',
        'design',
        'setup',
        'custoproc',
        'unid',
        'armazem',
        'origem',
        'paragem',
        'tcusto',
        'oristamp',
        'ousrinis',
        'ousrdata',
        'ourshora',
        'usrinis',
        'usrdata',
        'usrhora',
        'regauto',
        'responsavel',
        'fauser'
    ];

    public function interruptReason() : BelongsTo {
        return $this->belongsTo(InterruptReasons::class, "tabprstamp", "u_tabprstamp");
    }

    public function newUniqueId() : string
    {
        return 'WEB' . date('YmdHis') . '.' . substr(Str::uuid(), 0, 11);
    }

    public function isValidUniqueId($value): bool
    {
        return true;
    }
}
