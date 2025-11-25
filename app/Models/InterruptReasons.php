<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterruptReasons extends Model
{
    /**
     * Tabela associada ao modelo
     */
    protected $table = "u_tabpr";

    protected $primaryKey = 'u_tabprstamp';

    public $incrementing =  false;

    protected $keyType = 'string';
}
