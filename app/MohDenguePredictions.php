<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MohDenguePredictions extends Model
{
    //
    protected $fillable = ['moh','ol_moh_id','year','week','cases','algorithm'];
}
