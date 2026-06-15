<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SubOffice extends Model
{
    use HasFactory,SoftDeletes;
     protected $table = 'sub_office_tbl';

    protected $fillable = [
        'main_office_id',
        'sub_office_name',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function mainOffice()
    {
        return $this->belongsTo(MainOffice::class, 'main_office_id');
    }
}

