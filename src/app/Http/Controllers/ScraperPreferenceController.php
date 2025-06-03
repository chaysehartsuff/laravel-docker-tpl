<?php

namespace App\Http\Controllers;

use App\Models\ScraperPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScraperPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('scraper-preferences.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $scraperPreference = new ScraperPreference();
        $scraperPreference->description = $request->get('description');
        $scraperPreference->type = $request->get('type');
        $scraperPreference->name = $request->get('name');
        $scraperPreference->user_id = Auth::id();
        $scraperPreference->save();

        return redirect()->route('dashboard')->with('success', 'Scraper preference created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ScraperPreference $scraperPreference)
    {
        // To be implemented
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScraperPreference $scraperPreference)
    {
        // To be implemented
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:scraper_preferences,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $scraperPreference = ScraperPreference::findOrFail($request->id);
        $scraperPreference->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard')->with('success', 'Scraper preference updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScraperPreference $scraperPreference)
    {
        // To be implemented
    }
}