<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncomingDocumentRoute;
use App\Models\IncomingDocument;
use Auth;
use App\Models\SubOffice;


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
public function get_data_receive_incoming(Request $request){
    $search = $request->query('search');
    $perPage = $request->query('per_page', 10);
    
    $inprogress = IncomingDocumentRoute::with('document.documentType')->with('receivedBy')
        ->with('document.documentRoute.office', 'document.documentRoute.receivedBy')
        ->where('status', '=', 'RECEIVED')
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

public function receive_secretariat_incoming(Request $request, $id){
 try {
        // Find the permit/application or fail
        $incoming = IncomingDocumentRoute::findOrFail($id);
        
        // Check if already received
        if ($incoming->status === 'RECEIVED') {
            return response()->json([
                'success' => false,
                'message' => 'Incoming Document has already been received'
            ], 422);
        }
        
        // Get current date and time
        $date_receive = now(); // Format: YYYY-MM-DD
      
        
        // Update the permit with receive information
        $incoming->update([
            'date_receive' => $date_receive,
            'receive_user_id' => Auth::user()->id,
            'status' => 'RECEIVED'
        ]);
        
        // Load relationships for complete data
       
        
        // Return success response
        return response()->json([
            'success' => true,
           'message' => 'Incoming Document received successfully. Document transferred to Receive Tab.',
            'data' => $incoming->fresh()
        ], 200);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // If permit not found
        return response()->json([
            'success' => false,
            'message' => 'Application not found'
        ], 404);
        
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error receiving application: ' . $e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'Failed to receive application. Please try again.',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }

}
  public function route_office_secretariat()
        {
             $office = SubOffice::where('status', 'Active')->where('id','!=',Auth::user()->sub_office_id)
                 ->get();

                return response()->json([
                    'status' => true,
                    'data' => $office
                ], 200);
        }

} 
   
