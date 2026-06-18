<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;  // ← ADD THIS LINE


class IncomingDocument extends Model
{
    use HasFactory, SoftDeletes;
   protected $table = 'incoming_documents_tbl';

   protected $fillable = [
        'tracking_number',
        'token',
        'document_type_id',
        'document_classification',
        'sender_name',
        'subject',
        'date_receive',
        'time_receive',
        'date_release',
        'time_release',
        'draft_attachment',
        'release_attachment',
        'remarks',
        'status',
        'receive_user_id',
        'release_user_id',
    ];
     protected $casts = [
        'date_receive' => 'date',
        'date_release' => 'date',
        'time_receive' => 'datetime:H:i',
        'time_release' => 'datetime:H:i',
    ];
     public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
public function documentRoute()
{
    return $this->hasMany(IncomingDocumentRoute::class, 'document_id');
}

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'receive_user_id');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'release_user_id');
    }
    public static function generateTrackingNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $prefix = "26-RO-{$date}-";
        
        // Get the last tracking number for today
        $lastDocument = self::where('tracking_number', 'like', "{$prefix}%")
            ->orderBy('tracking_number', 'desc')
            ->first();

        if ($lastDocument) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastDocument->tracking_number, -5);
            $newSequence = str_pad($lastSequence + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Start with 00001
            $newSequence = '00001';
        }

        return $prefix . $newSequence;
    }

}
