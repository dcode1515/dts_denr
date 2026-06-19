<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncomingDocumentRoute;
use App\Models\IncomingDocument;
use Auth;


class SecretariatController extends Controller
{
    //

    public function dashboard(){
        return view ('secretariat.dashboard');
    }
    public function incoming_secretariat(){
         return view ('secretariat.incoming');
    }
  public function get_data_pending_incoming(Request $request){
    $search = $request->query('search');
    $perPage = $request->query('per_page', 10);
    
    $inprogress = IncomingDocumentRoute::with('document.documentType')
        ->with('document.documentRoute.office', 'document.documentRoute.receivedBy')
        ->where('status', '=', 'PENDING')
        ->where('office_id', Auth::user()->sub_office_id)
        ->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('date_forwarded', 'like', '%' . $search . '%')
                    ->orWhereHas('document', function ($subQ) use ($search) {
                        $subQ->where('tracking_number', 'like', '%' . $search . '%')
                            ->orWhere('sender_name', 'like', '%' . $search . '%')
                            ->orWhere('subject', 'like', '%' . $search . '%')
                            ->orWhere('document_classification', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('document.documentType', function ($subQ) use ($search) {
                        $subQ->where('document_type_name', 'like', '%' . $search . '%');
                    });
            });
        })
        ->orderBy(IncomingDocument::select('tracking_number')
            ->whereColumn('incoming_documents_tbl.id', 'incoming_document_route.document_id'), 'desc')
        ->paginate($perPage);
    
    return response()->json([
        'success' => true,
        'data' => $inprogress
    ]);
}
} 
   
