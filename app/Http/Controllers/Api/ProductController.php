<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Product;
use App\Models\Team;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['venue', 'homeTeam', 'awayTeam', 'competition'])
            ->orderBy('starts_at');

        if ($request->filled('teams')) {
            $teamIds = explode(',', $request->input('teams'));
            $query->where(function ($q) use ($teamIds) {
                $q->whereIn('home_team_id', $teamIds)
                    ->orWhereIn('away_team_id', $teamIds);
            });
        }

        if ($request->filled('competitions')) {
            $competitionIds = explode(',', $request->input('competitions'));
            $query->whereIn('competition_id', $competitionIds);
        }

        if ($request->filled('venues')) {
            $venueIds = explode(',', $request->input('venues'));
            $query->whereIn('venue_id', $venueIds);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->paginate(24);

        return response()->json($products);
    }

    public function filters(): JsonResponse
    {
        $teamIds = Product::whereNotNull('home_team_id')->pluck('home_team_id')
            ->merge(Product::whereNotNull('away_team_id')->pluck('away_team_id'))
            ->unique();

        $teams = Team::whereIn('id', $teamIds)->orderBy('name')->get(['id', 'name']);

        $competitions = Competition::whereIn('id',
            Product::whereNotNull('competition_id')->distinct()->pluck('competition_id')
        )->orderBy('name')->get(['id', 'name']);

        $venues = Venue::whereIn('id',
            Product::whereNotNull('venue_id')->distinct()->pluck('venue_id')
        )->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'teams' => $teams,
            'competitions' => $competitions,
            'venues' => $venues,
        ]);
    }
}
