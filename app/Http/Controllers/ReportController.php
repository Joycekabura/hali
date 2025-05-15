<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'report'=> 'required|string',
            'description'=> 'nullable|string',
            'location'=> 'required|array',
            'location.latitude'=> 'required|numeric',
            'location.longitude'=> 'required|numeric',
        ]);
        
        $report = Report::create([
            'title'=> $request->report,
            'description'=> $request->description,
            'latitude'=> $request->location['latitude'],
            'longitude'=> $request->location['longitude'],
            'user_id'=> Auth::id()
        ]);
        return response()->json($report, 201);
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