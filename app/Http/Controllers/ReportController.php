<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all reports for the map view
        $reports = Report::all();
        return response()->json($reports);
    }
    
    /**
     * Get reports created by the authenticated user
     */
    public function userReports()
    {
        $reports = Report::where('user_id', Auth::id())->get();
        return response()->json($reports);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $request->validate([
            'incidentType'=> 'required|string',
            'description'=> 'nullable|string',
            'location'=> 'required',
        ]);
        
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        // Log the request data for debugging
        \Log::info('Report creation attempt', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);
        
        try {
            // Extract latitude and longitude from the location object
            $latitude = null;
            $longitude = null;
            
            if ($request->has('location')) {
                if (is_array($request->location)) {
                    // If location is sent as an array with lat/lng properties
                    $latitude = $request->location['latitude'] ?? null;
                    $longitude = $request->location['longitude'] ?? null;
                } elseif (is_object($request->location)) {
                    // If location is sent as an object
                    $location = $request->location;
                    $latitude = $location->latitude ?? null;
                    $longitude = $location->longitude ?? null;
                }
            }
            
            if (!$latitude || !$longitude) {
                return response()->json(['error' => 'Invalid location format'], 400);
            }
            
            $report = Report::create([
                'title'=> $request->incidentType, // Changed from report to incidentType
                'description'=> $request->description,
                'latitude'=> $latitude,
                'longitude'=> $longitude,
                'user_id'=> Auth::id()
            ]);
            
            // Log successful creation
            \Log::info('Report created successfully', ['report_id' => $report->id]);
            
            return response()->json($report, 201);
        } catch (\Exception $e) {
            // Log any errors that occur
            \Log::error('Failed to create report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Failed to create report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // This method can be used as an alternative endpoint for creating reports
        return $this->create($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        return response()->json($report);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        if($report->user_id !== Auth::id()){
            return response()->json(['message'=>'Reported incident not found'],404);
        }
        
        $request->validate([
            'report'=> 'sometimes|required|string',
            'description'=> 'sometimes|nullable|string',
        ]);
        
        $report->update([
            'title'=> $request->report ?? $report->title,
            'description'=> $request->description ?? $report->description,
        ]);
        
        return response()->json($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
    
        if($report->user_id !== Auth::id()){
            return response()->json(['message'=>'Reported incident not found'],404);
        }
        $report->delete();
        return response()->json(['message'=>'Reported incident Deleted']);
    }
}