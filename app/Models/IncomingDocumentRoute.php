<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class IncomingDocumentRoute extends Model
{
       use HasFactory, SoftDeletes;
       protected $table = 'incoming_document_route';
      protected $fillable = [
            'document_id',
            'office_id',
            'from_office_id',
            'action_id',
            'receive_user_id',
            'date_forwarded',
            'date_receive',
            'date_document_out',
            'date_acted',
            'memo_slip_action',
            'acted_documents',
            'memo_slip_action',
            'user_acted_id',
            'remarks',
            'status',
        ];
          protected $casts = [
           
            'date_forwarded' => 'datetime',
            'date_receive' => 'datetime',
            'date_document_out' => 'datetime',
         ];
      public function document()
    {
        return $this->belongsTo(IncomingDocument::class, 'document_id');
    }
    public function office()
    {
        return $this->belongsTo(SubOffice::class, 'office_id');
    }
  


    public function action()
    {
        return $this->belongsTo(DocumentAction::class, 'action_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'receive_user_id');
    }


}
