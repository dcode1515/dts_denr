<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentType;
use App\Models\SubOffice;
use Illuminate\Support\Facades\Storage;
use App\Models\IncomingDocument;
use App\Models\IncomingDocumentRoute;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\DB;  // ← ADD THIS LINE
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;  // ← ADD THIS LINE
use Illuminate\Support\Facades\Validator;


class AdminController extends Controller
{
    //
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    public function document_type()
    {
        return view('admin.document_type');
    }
    public function store_document_type(Request $request)
    {
        $request->validate([
             'name' => 'required|string|max:255|unique:document_type_tbl,document_type_name',
             'code' => 'required|string|max:255|unique:document_type_tbl,code',
             'status' => 'required|in:Active,Inactive'
        ]);

        try {
            // Create a new DocumentType instance and save it to the database
            $documentType = new DocumentType();
            $documentType->document_type_name = $request->input('name');
            $documentType->code = $request->input('code');
            $documentType->status = $request->input('status');
            $documentType->save();

           return response()->json(['message' => 'Document type created successfully', 'data' => $documentType], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create document type', 'error' => $e->getMessage()], 500);
        }
    }
    public function get_document_types(Request $request){
         $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided
    
        // Query promotions with optional search and sorting by 'date_original_appointment'
        $documentType = DocumentType::query()
            ->when($search, function ($query, $search) {
                return $query
                    ->where('document_type_name', 'like', '%' . $search . '%')
                     ->OrWhere('code', 'like', '%' . $search . '%')
                      ->OrWhere('status', 'like', '%' . $search . '%')
                        ->OrWhere('deleted_at', 'like', '%' . $search . '%')
                           ->OrWhere('created_at', 'like', '%' . $search . '%')
                            ->OrWhere('updated_at', 'like', '%' . $search . '%');
                   
            })
        
            ->orderBy('document_type_name', 'desc') // Order by date_original_appointment first
            ->paginate($perPage); // Use the per_page value for pagination
    
        return response()->json([
            'success' => true,
            'data' => $documentType
        ]);
    }
      public function update_document_type(Request $request, $id){
        // Validate the incoming request data
        $request->validate([
             'name' => 'required|string|max:255|unique:document_type_tbl,document_type_name,'.$id,
             'code' => 'required|string|max:255|unique:document_type_tbl,code,'.$id,
             'status' => 'required|in:Active,Inactive'
        ]);

        try {
            // Create a new PermitType instance and save it to the database
            $documentType =  DocumentType::find($id);
             $documentType->document_type_name = $request->input('name');
            $documentType->code = $request->input('code');
            $documentType->status = $request->input('status');
            $documentType->save();

           return response()->json(['message' => 'Document type updated successfully', 'data' => $documentType], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update document type', 'error' => $e->getMessage()], 500);
        }
    }
     public function delete_document_type($id)
{
    try {
        // Find the document type by ID
        $documentType = DocumentType::find($id);
        
        // Check if record exists
        if (!$documentType) {
            return response()->json(['message' => 'Document type not found'], 404);
        }
        
        // Delete the document type
        $documentType->delete();
        
        return response()->json(['message' => 'Document type deleted successfully'], 200);
        
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to delete document type', 'error' => $e->getMessage()], 500);
    }
    
}
    public function incoming_documents()
        {
            return view('admin.incoming_documents');
        }

        public function storeReserveTracking(Request $request)
{
    try {
        $createdDocuments = [];
        $count = 20; // Create exactly 20 records
        $userId = auth()->user()->id; // Get authenticated user ID

        for ($i = 0; $i < $count; $i++) {
            // Generate unique tracking number
            $trackingNumber = IncomingDocument::generateTrackingNumber();

            $document = IncomingDocument::create([
                'tracking_number' => $trackingNumber,
                'status' => 'RESERVE TRACKING',
                'receive_user_id' => $userId,
            ]);

            $createdDocuments[] = $document;

            // Small delay to ensure unique tracking numbers when creating rapidly
            usleep(1000); // 1ms delay
        }

        Log::info('Reserve tracking created', [
            'count' => $count,
            'user_id' => $userId,
            'created_at' => now()
        ]);

        // Format the documents for response using array_map
        $formattedDocuments = array_map(function($doc) {
            return [
                'id' => $doc->id,
                'tracking_number' => $doc->tracking_number,
                'status' => $doc->status,
                'receive_user_id' => $doc->receive_user_id,
                'created_at' => $doc->created_at,
            ];
        }, $createdDocuments);

        return response()->json([
            'success' => true,
            'message' => "Successfully created {$count} reserve tracking documents",
            'data' => [
                'count' => $count,
                'documents' => $formattedDocuments,
            ]
        ], 201);

    } catch (\Exception $e) {
        Log::error('Reserve tracking failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to create reserve tracking',
            'error' => $e->getMessage()
        ], 500);
    }
}
  public function get_data_reserve_tracking(Request $request){
         $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided
    
        // Query promotions with optional search and sorting by 'date_original_appointment'
        $tracking = IncomingDocument::where('status', 'RESERVE TRACKING')->where('receive_user_id', auth()->user()->id)
            ->when($search, function ($query, $search) {
                return $query
                    ->where('tracking_number', 'like', '%' . $search . '%');
            })
        
            ->orderBy('tracking_number', 'asc') // Order by date_original_appointment first
            ->paginate($perPage); // Use the per_page value for pagination
    
        return response()->json([
            'success' => true,
            'data' => $tracking
        ]);
    }
       public function create_incoming($id)
        {
            $incoming = IncomingDocument::find($id);
            return view('admin.create_incoming',compact('incoming'));
        }
        public function view_document($token)
            {
                $incoming = IncomingDocument::where('token', $token)->firstOrFail();
                return view('admin.update_incoming', compact('incoming'));
            }
          public function documentType()
        {
           $documentType = DocumentType::orderBy('created_at', 'asc')
            ->select('id', 'document_type_name')
            ->where('status', 'Active')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $documentType
        ], 200);
    }

   public function route_office()
        {
            $office = SubOffice::whereIn('sub_office_name', [
                    'OFFICE OF THE REGIONAL EXECUTIVE DIRECTOR - (ORED)',
                    'OFFICE OF THE ARD FOR MANAGEMENT SERVICES - (ARD-MS)',
                    'OFFICE OF THE ARD FOR TECHINICAL SERVICES - (ARD-TS)'
                ])
                ->where('status', 'Active')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $office
            ], 200);
        }

      public function forward_document(Request $request)
{
    $validator = Validator::make($request->all(), [
        'document_id' => 'required|integer|exists:incoming_documents_tbl,id',
        'tracking_number' => 'required|string',
        'offices' => 'required|array|min:1',
        'offices.*' => 'integer|exists:sub_office_tbl,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $document = IncomingDocument::findOrFail($request->document_id);
        
        // Check if document exists
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        // Prepare routes data for multiple offices
        $currentDateTime = now();
        $routesData = [];
        $newOfficeIds = [];
        $existingOffices = []; // Track offices that already have PENDING or RECEIVED status
        
        foreach ($request->offices as $officeId) {
            // Check if route already exists with PENDING or RECEIVED status
            $existingRoute = IncomingDocumentRoute::where('document_id', $document->id)
                ->where('office_id', $officeId)
                ->whereIn('status', ['PENDING', 'RECEIVED'])
                ->first();
            
            if ($existingRoute) {
                // Get office name for the message
                $office = SubOffice::find($officeId);
                $existingOffices[] = [
                    'office_id' => $officeId,
                    'office_name' => $office ? $office->sub_office_name : 'Unknown Office',
                    'status' => $existingRoute->status,
                    'date_forwarded' => $existingRoute->date_forwarded
                ];
                continue; // Skip this office
            }
            
            // Office doesn't have active route, add it
            $routesData[] = [
                'document_id' => $document->id,
                'office_id' => $officeId,
                'from_office_id' => Auth::user()->id,
                'date_forwarded' => $currentDateTime,
                'status' => "PENDING",
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ];
            $newOfficeIds[] = $officeId;
        }

        // If all selected offices already have active routes
        if (empty($routesData)) {
            return response()->json([
                'success' => false,
                'message' => 'All selected office(s) already have existing routes with PENDING or RECEIVED status',
                'existing_offices' => $existingOffices
            ], 422);
        }

        // Insert new routes
        IncomingDocumentRoute::insert($routesData);

        // Update document status
        $document->status = "IN-PROGRESS";
        $document->updated_at = $currentDateTime;
        $document->save();

        // Build response message
        $newCount = count($newOfficeIds);
        $existingCount = count($existingOffices);
        
        $message = "Document forwarded successfully to {$newCount} office(s).";
        if ($existingCount > 0) {
            $message .= " {$existingCount} office(s) were skipped because they already have existing routes.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'document_id' => $document->id,
                'tracking_number' => $document->tracking_number,
                'new_routes_count' => $newCount,
                'new_office_ids' => $newOfficeIds,
                'skipped_offices_count' => $existingCount,
                'skipped_offices' => $existingOffices,
                'forwarded_by' => Auth::user()->id,
                'date_forwarded' => $currentDateTime->toDateTimeString(),
            ]
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Forward document error: ' . $e->getMessage(), [
            'document_id' => $request->document_id,
            'user_id' => Auth::user()->id,
            'offices' => $request->offices,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to forward document',
            'error' => $e->getMessage()
        ], 500);
    }
}


       public function create_update_document(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'document_type' => 'required',
        'document_classification' => 'required',
        'date_received' => 'required|date',
        'time_received' => 'required',
        'sender_name' => 'required|string|min:2|max:100',
        'subject' => 'required|string|min:5|max:500',
        'draft_attachment' => 'nullable|file|mimes:pdf',
        'route_to' => 'nullable|array',                    // key must match frontend
        'route_to.*' => 'integer|exists:sub_office_tbl,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $document = IncomingDocument::findOrFail($id);

        // Handle file upload (unchanged)...
        if ($request->hasFile('draft_attachment')) {
            if ($document->draft_attachment) {
                Storage::disk('public')->delete($document->draft_attachment);
            }
            $file = $request->file('draft_attachment');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs(
                'attachments/' . $document->tracking_number,
                $fileName,
                'public'
            );
            $document->draft_attachment = $attachmentPath;
        }

        // Update document fields
        $document->document_type_id = $request->document_type;
        $document->date_receive = $request->date_received;
        $document->time_receive = $request->time_received . ':00';
        $document->sender_name = strtoupper($request->sender_name);
        $document->subject = strtoupper($request->subject);
        $document->document_classification = strtoupper($request->document_classification);
        $document->status = "IN-PROGRESS";
         $document->token = Str::random(64);
        $document->save();

        // --- Handle Route To (sync) ---
        // Delete all existing routes for this document
      //  IncomingDocumentRoute::where('document_id', $document->id)->delete();

        // Insert new routes if provided
        if ($request->has('route_to') && is_array($request->route_to)) {
            $currentDateTime = now();
            $routesData = [];
            foreach ($request->route_to as $officeId) {
                $routesData[] = [
                    'document_id' => $document->id,
                    'office_id' => $officeId,
                     'from_office_id' => Auth::user()->sub_office_id,
                    'date_forwarded' => $currentDateTime,
                    'status' => "PENDING",
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                ];
            }
            IncomingDocumentRoute::insert($routesData);
        }

        

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => [
                'tracking_number' => $document->tracking_number,
                'document_id' => $document->id,
                'routed_offices_count' => $request->has('route_to') ? count($request->route_to) : 0
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update document',
            'error' => $e->getMessage()
        ], 500);
    }
}


 public function update_document(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'document_type' => 'required',
        'document_classification' => 'required',
        'date_received' => 'required|date',
        'time_received' => 'required',
        'sender_name' => 'required|string|min:2|max:100',
        'subject' => 'required|string|min:5|max:500',
        'draft_attachment' => 'nullable|file|mimes:pdf',
      
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $document = IncomingDocument::findOrFail($id);

        // Handle file upload (unchanged)...
        if ($request->hasFile('draft_attachment')) {
            if ($document->draft_attachment) {
                Storage::disk('public')->delete($document->draft_attachment);
            }
            $file = $request->file('draft_attachment');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs(
                'attachments/' . $document->tracking_number,
                $fileName,
                'public'
            );
            $document->draft_attachment = $attachmentPath;
        }

        // Update document fields
        $document->document_type_id = $request->document_type;
        $document->date_receive = $request->date_received;
        $document->time_receive = $request->time_received . ':00';
        $document->sender_name = $request->sender_name;
        $document->subject = $request->subject;
        $document->document_classification = $request->document_classification;
        $document->status = "IN-PROGRESS";
        $document->save();

        // --- Handle Route To (sync) ---
        // Delete all existing routes for this document
      //  IncomingDocumentRoute::where('document_id', $document->id)->delete();

        // Insert new routes if provided
      
        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => [
                'tracking_number' => $document->tracking_number,
                'document_id' => $document->id,
             
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update document',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function get_data_in_progress(Request $request){
         $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided
    
        // Query promotions with optional search and sorting by 'date_original_appointment'
        $inprogress = IncomingDocument::with('documentType')->with('documentRoute.office','documentRoute.receivedBy')->where('status', 'IN-PROGRESS')
            ->when($search, function ($query, $search) {
                return $query
                    ->where('tracking_number', 'like', '%' . $search . '%')
                     ->OrWhere('sender_name', 'like', '%' . $search . '%')
                      ->OrWhere('subject', 'like', '%' . $search . '%')
                         ->OrWhere('document_classification', 'like', '%' . $search . '%')
                       ->OrWhereHas('documentType', function ($q) use ($search) {
                            $q->where('document_type_name', 'like', '%' . $search . '%');
                        });
            })
        
            ->orderBy('tracking_number', 'desc') // Order by date_original_appointment first
            ->paginate($perPage); // Use the per_page value for pagination
    
        return response()->json([
            'success' => true,
            'data' => $inprogress
        ]);
    }
    public function get_data_for_release(Request $request){
         $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided
    
        // Query promotions with optional search and sorting by 'date_original_appointment'
        $inprogress = IncomingDocument::with('documentType')->with('documentRoute.office','documentRoute.receivedBy')->where('status', 'FOR RELEASE')
            ->when($search, function ($query, $search) {
                return $query
                    ->where('tracking_number', 'like', '%' . $search . '%')
                     ->OrWhere('sender_name', 'like', '%' . $search . '%')
                      ->OrWhere('subject', 'like', '%' . $search . '%')
                         ->OrWhere('document_classification', 'like', '%' . $search . '%')
                       ->OrWhereHas('documentType', function ($q) use ($search) {
                            $q->where('document_type_name', 'like', '%' . $search . '%');
                        });
            })
        
            ->orderBy('tracking_number', 'desc') // Order by date_original_appointment first
            ->paginate($perPage); // Use the per_page value for pagination
    
        return response()->json([
            'success' => true,
            'data' => $inprogress
        ]);
    }

    public function route_office_other(){
    $office = SubOffice::where('status', 'Active')
                 ->get();

                return response()->json([
                    'status' => true,
                    'data' => $office
                ], 200);
    }


            
}
