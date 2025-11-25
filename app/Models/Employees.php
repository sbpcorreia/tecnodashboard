<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    protected $table = 'pe';
    protected $primaryKey = 'pestamp';
    public $incrementing = false;
    protected $keyType = 'string';
}
