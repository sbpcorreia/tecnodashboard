<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhcUser extends Model
{
    protected $table = "us";

    protected $primaryKey = "usstamp";

    public $incrementing = false;

    protected $keyType = 'string';
}
