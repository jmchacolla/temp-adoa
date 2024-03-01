<?php

namespace ProcessMaker\Package\PackageZjAdoa\Models;

use Illuminate\Database\Eloquent\Model;

class AdoaTypeAppraisalDetail extends Model
{
    protected $table   = 'adoa_type_appraisal_detail';
    public $timestamps = true;

    protected $fillable = [
        'description'
    ];
}
