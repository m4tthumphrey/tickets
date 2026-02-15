<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Product;
use App\Models\Team;

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
            $query->whereIn('home_team_id', $teamIds);
        }

        if ($request->filled('competitions')) {
            $competitionIds = explode(',', $request->input('competitions'));
            $query->whereIn('competition_id', $competitionIds);
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
        $teams = Team::whereIn('id',
            Product::whereNotNull('home_team_id')->distinct()->pluck('home_team_id')
        )->orderBy('name')->get(['id', 'name']);

        $competitions = Competition::whereIn('id',
            Product::whereNotNull('competition_id')->distinct()->pluck('competition_id')
        )->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'teams' => $teams,
            'competitions' => $competitions,
        ]);
    }
}
