<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MemoSlip extends Model
{
    use HasFactory, SoftDeletes;
     protected $table = 'memo_slip_tbl';

    protected $primaryKey = 'id';

    protected $fillable = [
        'memo_slip_name',
        'status',
    ];

    protected $casts = [
         'office_id' => 'integer',
         'date_created' => 'date',
          
    ];
}
