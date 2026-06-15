<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MainOffice extends Model
{
    use HasFactory, SoftDeletes;
  protected $table = 'main_office_tbl';

    protected $fillable = [
        'main_office_name',
        'tracking_code',
        'head_office',
        'address',
        'email',
        'contact_number',
        'status',
    ];
     protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

}
