<?php

namespace App\Http\Controllers;

use App\Models\ScraperPreference;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Jobs\FindScraperPreferenceLinks;

class ScraperController extends Controller
{
    /**
     * Run the scraper for a specific ScraperPreference.
     *
     * @param ScraperPreference $scraperPreference
     * @return JsonResponse
     */
    public function run(ScraperPreference $scraperPreference): JsonResponse
    {
        if ($scraperPreference->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        request()->validate([
            'url_count' => 'required|integer|min:1',
            'name' => [
                'required',
                function ($attribute, $value, $fail) {
                    $allowedNames = ['sp.links'];
                    if (!in_array($value, $allowedNames)) {
                        $fail("The $attribute must be one of: " . implode(', ', $allowedNames));
                    }
                }
            ]
        ]);

        $job = null;

        switch (request('name')) {
            case 'sp.links':
                $job = new FindScraperPreferenceLinks($scraperPreference, request()->get('url_count'));
                break;
        }

        if ($job) {
            dispatch($job);
            return response()->json(['message' => 'Job started successfully'], 202);
        } else {
            return response()->json(['message' => 'No matching job found for the given name'], 400);
        }
    }

    /**
     * Get all status codes for authed user.
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            $user_statuses = Status::where('user_id', Auth::id())
                ->select('code', 'meta_id', 'content')
                ->get();
            return response()->json([
                'statuses' => $user_statuses
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve scraper status.');
        }
    }

    /**
     * Handle exceptions and return appropriate JSON response.
     *
     * @param \Exception $e
     * @param string $defaultMessage
     * @return JsonResponse
     */
    private function handleException(\Exception $e, string $defaultMessage): JsonResponse
    {
        $message = config('app.debug') ? $e->getMessage() : 'Something went wrong';
        
        return response()->json([
            'message' => $defaultMessage,
            'error' => $message
        ], 500);
    }
}