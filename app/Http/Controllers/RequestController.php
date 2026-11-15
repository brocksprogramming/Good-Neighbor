<?php
namespace App\Http\Controllers;

use App\Models\MicroRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'radius' => ['sometimes', 'numeric', 'in:1,3,5,10'],
        ]);
        $radius = isset($validated['radius']) ? (float) $validated['radius'] : 5;
        $location = $request->session()->get('request_location');
        $nearbyRequests = collect();

        if (is_array($location)) {
            $nearbyRequests = MicroRequest::query()
                ->where('status', 'open')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->nearby($location['latitude'], $location['longitude'], $radius)
                ->get();
        }

        return view('index', [
            'nearbyRequests' => $nearbyRequests,
            'radius' => $radius,
            'hasLocation' => is_array($location),
        ]);
    }

    public function setLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'numeric', 'in:1,3,5,10'],
        ]);

        $request->session()->put('request_location', [
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
        ]);

        return redirect()->route('requests.index', ['radius' => $validated['radius']]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'urgency' => ['required', 'in:low,medium,high,immediate'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        MicroRequest::create([
            ...$validated,
            'latitude' => round((float) $validated['latitude'], 3),
            'longitude' => round((float) $validated['longitude'], 3),
            'status' => 'open',
            'expires_at' => now()->addDays(14),
        ]);

        $request->session()->put('request_location', [
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
        ]);

        return redirect()
            ->route('requests.index')
            ->with('success', 'Your request is posted. Neighbors nearby can now find it.');
    }
}
