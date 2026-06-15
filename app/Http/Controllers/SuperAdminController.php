<?php

namespace App\Http\Controllers;

use App\Models\MainOffice;
use App\Models\SubOffice;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    //

    public function index()
    {
        return view('superadmin.dashboard');
    }
    public function main_office()
    {
        return view('superadmin.main-office');
    }
    public function sub_office()
    {
        return view('superadmin.sub-office');
    }

  public function store_main_office(Request $request){
        // Validate the incoming request data
        $request->validate([
             'main_office_name' => 'required|string|max:255|unique:main_office_tbl,main_office_name',
             'tracking_code' => 'required',
             'head_office' => 'required',
             'address' => 'required',
              'email' => 'required|email',
              'contact_number' => 'required|string|max:50',
        ]);

        try {
            // Create a new PermitType instance and save it to the database
            $officeHead = new MainOffice();
            $officeHead->main_office_name = $request->input('main_office_name');
            $officeHead->tracking_code = $request->input('tracking_code');
            $officeHead->head_office = $request->input('head_office');
            $officeHead->address = $request->input('address');
            $officeHead->email = $request->input('email');
            $officeHead->contact_number = $request->input('contact_number');
            $officeHead->status = "Active";
            $officeHead->save();

           return response()->json(['message' => 'Main Office created successfully', 'data' => $officeHead], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create main office', 'error' => $e->getMessage()], 500);
        }
    }

    public function update_main_office(Request $request, $id){
        // Validate the incoming request data
        $request->validate([
             'main_office_name' => 'required|string|max:255|unique:main_office_tbl,main_office_name,'.$id,
             'tracking_code' => 'required',
             'head_office' => 'required',
             'address' => 'required',
              'email' => 'required|email',
              'contact_number' => 'required|string|max:50',
        ]);

        try {
            // Create a new PermitType instance and save it to the database
            $officeHead =  MainOffice::find($id);
            $officeHead->main_office_name = $request->input('main_office_name');
            $officeHead->tracking_code = $request->input('tracking_code');
            $officeHead->head_office = $request->input('head_office');
            $officeHead->address = $request->input('address');
            $officeHead->email = $request->input('email');
            $officeHead->contact_number = $request->input('contact_number');
            $officeHead->status = "Active";
            $officeHead->save();

           return response()->json(['message' => 'Main Office updated successfully', 'data' => $officeHead], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create main office', 'error' => $e->getMessage()], 500);
        }
    }
    public function delete_main_office($id)
{
    try {
        // Find the main office by ID
        $mainOffice = MainOffice::find($id);
        
        // Check if record exists
        if (!$mainOffice) {
            return response()->json(['message' => 'Main office not found'], 404);
        }
        
        // Delete the main office
        $mainOffice->delete();
        
        return response()->json(['message' => 'Main office deleted successfully'], 200);
        
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to delete main office', 'error' => $e->getMessage()], 500);
    }
    
}
  public function delete_sub_office($id)
{
    try {
        // Find the main office by ID
        $subOffice = SubOffice::find($id);
        
        // Check if record exists
        if (!$subOffice) {
            return response()->json(['message' => 'Sub office not found'], 404);
        }
        
        // Delete the sub office
        $subOffice->delete();
        
        return response()->json(['message' => 'Sub office deleted successfully'], 200);
        
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to delete sub office', 'error' => $e->getMessage()], 500);
    }
    
}


    public function get_main_offices(Request $request){
         $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided
    
        // Query promotions with optional search and sorting by 'date_original_appointment'
        $offices = MainOffice::query()
            ->when($search, function ($query, $search) {
                return $query
                    ->where('main_office_name', 'like', '%' . $search . '%')
                     ->OrWhere('tracking_code', 'like', '%' . $search . '%')
                      ->OrWhere('head_office', 'like', '%' . $search . '%')
                        ->OrWhere('address', 'like', '%' . $search . '%')
                           ->OrWhere('email', 'like', '%' . $search . '%')
                            ->OrWhere('contact_number', 'like', '%' . $search . '%');
                   
            })
        
            ->orderBy('head_office', 'desc') // Order by date_original_appointment first
            ->paginate($perPage); // Use the per_page value for pagination
    
        return response()->json([
            'success' => true,
            'data' => $offices
        ]);
    }

    public function get_data_main_office(){
        $offices = MainOffice::all();
        return response()->json(['data' => $offices], 200);
    }
    public function store_sub_office(Request $request){
         $request->validate([
        'main_office_id' => 'required|exists:main_office_tbl,id', // Add exists validation
        'sub_office_name' => 'required|string|max:255',
        ]);

    try {
        // Check if the same sub office name already exists under the SAME head office
        $existingSubOffice = \App\Models\SubOffice::where('main_office_id', $request->input('main_office_id'))
            ->where('sub_office_name', $request->input('sub_office_name'))
            ->first();

        // If exists under the same head office, return error
        if ($existingSubOffice) {
            return response()->json([
                'message' => 'This sub office name already exists under the selected head office',
                'errors' => [
                    'sub_office_name' => ['Sub office name already exists for this head office']
                ]
            ], 409); // 409 Conflict status code
        }

        // Create a new SubOffice instance and save it to the database
        $suboffice = new \App\Models\SubOffice();
        $suboffice->main_office_id = $request->input('main_office_id');
        $suboffice->sub_office_name = $request->input('sub_office_name');
       
        $suboffice->save();

        return response()->json([
            'message' => 'Sub Office created successfully', 
            'data' => $suboffice
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create Sub office', 
            'error' => $e->getMessage()
        ], 500);
    }
    }
       public function update_sub_office(Request $request, $id){
        // Validate the incoming request data
       $request->validate([
        'main_office_id' => 'required|exists:main_office_tbl,id', // Add exists validation
        'sub_office_name' => 'required|string|max:255',
    ]);

    try {
        // Find the existing sub office
        $suboffice = \App\Models\SubOffice::find($id);
        
        // If sub office doesn't exist, return error
        if (!$suboffice) {
            return response()->json([
                'message' => 'Sub office not found'
            ], 404);
        }

        // Check if the same sub office name already exists under the SAME head office
        // Excluding the current sub office being updated
        $existingSubOffice = \App\Models\SubOffice::where('main_office_id', $request->input('main_office_id'))
            ->where('sub_office_name', $request->input('sub_office_name'))
            ->where('id', '!=', $id) // Exclude current record
            ->first();

        // If exists under the same head office, return error
        if ($existingSubOffice) {
            return response()->json([
                'message' => 'This sub office name already exists under the selected head office',
                'errors' => [
                    'sub_office_name' => ['Sub office name already exists for this head office']
                ]
            ], 409); // 409 Conflict status code
        }

        // Update the SubOffice instance and save to database
        $suboffice->main_office_id = $request->input('main_office_id');
        $suboffice->sub_office_name = $request->input('sub_office_name');
        
        $suboffice->save();

        return response()->json([
            'message' => 'Sub Office updated successfully', 
            'data' => $suboffice
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update Sub office', 
            'error' => $e->getMessage()
        ], 500);
    }
    }
     public function get_sub_offices(Request $request){
         $search = $request->query('search');
        $perPage = $request->query('per_page', 10); // Default to 10 if not provided
    
        // Query promotions with optional search and sorting by 'date_original_appointment'
        $offices = SubOffice::with('mainOffice')
            ->when($search, function ($query, $search) {
                return $query
                    ->where('sub_office_name', 'like', '%' . $search . '%')
                    ->orWhereHas('mainOffice', function ($query) use ($search) {
                        $query->where('main_office_name', 'like', '%' . $search . '%');
                    });
                   
            })
        
            ->orderBy('sub_office_name', 'asc') // Order by date_original_appointment first
            ->paginate($perPage); // Use the per_page value for pagination
    
        return response()->json([
            'success' => true,
            'data' => $offices
        ]);
    }

}
