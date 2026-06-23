<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncomingDocumentRoute;
use App\Models\IncomingDocument;
use Auth;
use App\Models\SubOffice;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\Log;  // ← ADD THIS LINE


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

public function forward_other_office(Request $request, $id)
{
   $request->validate([
        'offices' => 'required|json',
        'selectedDuration' => 'required|string|max:255',
        'remarks' => 'nullable|string|max:500',
        'attachments.*' => 'nullable|file|mimes:pdf|max:20480', // 20MB max per file
    ]);

    try {
        // Decode offices from JSON
        $offices = json_decode($request->offices, true);
        
        if (empty($offices)) {
            return response()->json([
                'success' => false,
                'message' => 'No offices selected. Please select at least one office to forward the document.',
            ], 422);
        }

        // Find the current document route using the $id parameter
        // $id here is the route ID (IncomingDocumentRoute ID)
        $currentRoute = IncomingDocumentRoute::findOrFail($id);
        
        // Get the IncomingDocument using the document_id from the route
        $incomingDocument = IncomingDocument::findOrFail($currentRoute->document_id);

        // Get the authenticated user
        $user = Auth::user();
        $userOfficeId = $user->sub_office_id;

        // Get user office name for folder structure
        $userOfficeName = '';
        if ($userOfficeId) {
            $userOffice = \DB::table('sub_office_tbl')->where('id', $userOfficeId)->first();
            if ($userOffice) {
                $userOfficeName = $userOffice->sub_office_name ?? 'unknown';
            }
        }

        // Generate unique identifier for this forward action
        $forwardIdentifier = date('Ymd_His') . '_' . uniqid();
        
        // Create a unique folder name with office info
        $officeFolder = $userOfficeName . '_' . $userOfficeId . '_' . $forwardIdentifier;
        
        // Handle file attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            // Use tracking number directly for the folder path
            $trackingNumber = $incomingDocument->tracking_number ?? 'unknown';
            
            foreach ($request->file('attachments') as $file) {
                // Generate unique filename with timestamp and random string
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in: storage/app/public/attachments/tracking_number/ACTED DOCUMENTS/office_name_id_timestamp/
                $attachmentPath = $file->storeAs(
                    'attachments/' . $trackingNumber . '/ACTED DOCUMENTS/' . $officeFolder,
                    $fileName,
                    'public'
                );
                $attachmentPaths[] = $attachmentPath;
            }
        }
        
        $attachmentString = !empty($attachmentPaths) ? implode(',', $attachmentPaths) : null;

        // UPDATE: Update the IncomingDocument with duration and user
        $incomingDocument->set_user_duration_id = $user->id;
        $incomingDocument->duration = $request->selectedDuration;
        $incomingDocument->save();

        // UPDATE: Update the current route status to "FORWARDED"
        $currentRoute->status = "FORWARDED";
        $currentRoute->date_document_out = now();
        $currentRoute->acted_documents = $attachmentString;
        $currentRoute->save();

        // CREATE: Create new routes for each selected office
        $routes = [];
        $now = now();

        foreach ($offices as $office) {
            // Make sure office has an ID
            if (!isset($office['id'])) {
                continue;
            }

            $route = IncomingDocumentRoute::create([
                'document_id' => $incomingDocument->id, // Reference to the IncomingDocument
                'office_id' => $office['id'], // Target office ID from the selected offices
                'from_office_id' => $userOfficeId, // Source office ID (current user's office)
                'date_forwarded' => $now,
                'status' => 'PENDING',
                'remarks' => $request->remarks,
             
            ]);

            $routes[] = $route;
        }

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Document forwarded successfully to ' . count($routes) . ' office(s).',
            'data' => [
                'document_id' => $incomingDocument->id,
                'tracking_number' => $incomingDocument->tracking_number,
                'duration' => $request->selectedDuration,
                'date_forwarded' => $now->toDateTimeString(),
                'routes' => $routes,
               
            ]
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Document route not found for forwarding', [
            'route_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Document route not found. Please check the document and try again.',
        ], 404);

    } catch (\Exception $e) {
        Log::error('Forward document error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['attachments']),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to forward document. Please try again.',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
        
}
public function for_release_document(Request $request)
{
    $request->validate([
        'document_route_id' => 'required|exists:incoming_document_route,id',
        'tracking_number' => 'required|string|max:255',
        'remarks' => 'nullable|string|max:500',
        'attachments.*' => 'nullable|file|mimes:pdf|max:20480', // 20MB max per file
    ]);

    try {
        // Find the current document route
        $currentRoute = IncomingDocumentRoute::findOrFail($request->document_route_id);
        
        // Get the IncomingDocument using the document_id from the route
        $incomingDocument = IncomingDocument::findOrFail($currentRoute->document_id);

        // Get the authenticated user
        $user = Auth::user();
        $userOfficeId = $user->sub_office_id;

        // Get user office name for folder structure
        $userOfficeName = '';
        if ($userOfficeId) {
            $userOffice = \DB::table('sub_office_tbl')->where('id', $userOfficeId)->first();
            if ($userOffice) {
                $userOfficeName = $userOffice->sub_office_name ?? 'unknown';
            }
        }

        // Generate unique identifier for this release action
        $releaseIdentifier = date('Ymd_His') . '_' . uniqid();
        
        // Create a unique folder name with office info
        $officeFolder = $userOfficeName . '_' . $userOfficeId . '_' . $releaseIdentifier;
        
        // Handle file attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            // Use tracking number directly for the folder path
            $trackingNumber = $incomingDocument->tracking_number ?? 'unknown';
            
            foreach ($request->file('attachments') as $file) {
                // Generate unique filename with timestamp and random string
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in: storage/app/public/attachments/tracking_number/RELEASE DOCUMENTS/office_name_id_timestamp/
                $attachmentPath = $file->storeAs(
                    'attachments/' . $trackingNumber . '/ACTED DOCUMENTS/' . $officeFolder,
                    $fileName,
                    'public'
                );
                $attachmentPaths[] = $attachmentPath;
            }
        }
        
        $attachmentString = !empty($attachmentPaths) ? implode(',', $attachmentPaths) : null;

        // UPDATE: Update the IncomingDocument status to "FOR RELEASE"
        $incomingDocument->status = "FOR RELEASE";
        $incomingDocument->for_release_remarks = $request->remarks;
        $incomingDocument->save();

        // UPDATE: Update the current route status to "FOR RELEASE"
        $currentRoute->status = "FOR RELEASE TO RECORDS";
        $currentRoute->date_document_out = now();
        $currentRoute->acted_documents = $attachmentString;
        $currentRoute->remarks = $request->remarks;
        $currentRoute->save();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Document has been marked for release successfully.',
            'data' => [
                'document_id' => $incomingDocument->id,
                'tracking_number' => $incomingDocument->tracking_number,
                'status' => 'FOR RELEASE',
                'date_released' => now()->toDateTimeString(),
                'remarks' => $request->remarks,
                'attachments' => $attachmentString,
            ]
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Document route not found for release', [
            'route_id' => $request->document_route_id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Document route not found. Please check the document and try again.',
        ], 404);

    } catch (\Exception $e) {
        Log::error('Release document error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['attachments']),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to release document. Please try again.',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
} 
   
