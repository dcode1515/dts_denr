<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentType;

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
}
