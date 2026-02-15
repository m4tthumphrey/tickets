<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Competition;
use App\Models\Product;
use App\Models\Team;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $available = fn ($q) => $q->where('available', true);
        $accessCode = $this->getAccessCode($request);
        $allowedTeamIds = $this->getAllowedTeamIds($accessCode);

        $query = Product::with(['venue', 'homeTeam', 'awayTeam', 'competition'])
            ->withCount(['ticketOptions' => $available])
            ->withMin(['ticketOptions' => $available], 'price')
            ->withMax(['ticketOptions' => $available], 'price')
            ->when($allowedTeamIds !== null, fn (Builder $q) => $q->whereIn('home_team_id', $allowedTeamIds))
            ->orderBy('starts_at');

        if ($request->filled('teams')) {
            $teamIds = explode(',', $request->input('teams'));
            if ($allowedTeamIds !== null) {
                $teamIds = array_intersect($teamIds, $allowedTeamIds->all());
            }
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

        if (!$request->boolean('show_unavailable')) {
            $query->has('ticketOptions', '>', 0, 'and', $available);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->paginate(24);

        if ($accessCode) {
            $this->applyMarginToProducts($products->getCollection(), $accessCode);
        }

        return response()->json($products);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $accessCode = $this->getAccessCode($request);
        $allowedTeamIds = $this->getAllowedTeamIds($accessCode);

        if ($allowedTeamIds !== null && ! $allowedTeamIds->contains($product->home_team_id)) {
            abort(404);
        }

        $product->load([
            'venue',
            'homeTeam',
            'awayTeam',
            'competition',
            'ticketOptions' => fn ($q) => $q->where('available', true)->orderBy('price'),
            'ticketOptions.ticketCategory',
        ]);

        if ($accessCode) {
            foreach ($product->ticketOptions as $option) {
                $option->price = $accessCode->applyMargin($option->price);
            }
        }

        return response()->json($product);
    }

    public function filters(Request $request): JsonResponse
    {
        $allowedTeamIds = $this->getAllowedTeamIds($this->getAccessCode($request));

        $productQuery = Product::query()
            ->when($allowedTeamIds !== null, fn (Builder $q) => $q->whereIn('home_team_id', $allowedTeamIds));

        $teams = Team::whereIn('id',
            (clone $productQuery)->whereNotNull('home_team_id')->distinct()->pluck('home_team_id')
        )->orderBy('name')->get(['id', 'name']);

        $competitions = Competition::whereIn('id',
            (clone $productQuery)->whereNotNull('competition_id')->distinct()->pluck('competition_id')
        )->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'teams' => $teams,
            'competitions' => $competitions,
        ]);
    }

    private function getAccessCode(Request $request): ?AccessCode
    {
        $accessCodeId = $request->session()->get('access_code_id');

        if (! $accessCodeId) {
            return null;
        }

        return AccessCode::with('teams')->find($accessCodeId);
    }

    private function getAllowedTeamIds(?AccessCode $accessCode): ?Collection
    {
        if (! $accessCode || $accessCode->teams->isEmpty()) {
            return null;
        }

        return $accessCode->teams->pluck('id');
    }

    private function applyMarginToProducts(Collection $products, AccessCode $accessCode): void
    {
        foreach ($products as $product) {
            if ($product->ticket_options_min_price !== null) {
                $product->ticket_options_min_price = $accessCode->applyMargin($product->ticket_options_min_price);
            }
            if ($product->ticket_options_max_price !== null) {
                $product->ticket_options_max_price = $accessCode->applyMargin($product->ticket_options_max_price);
            }
        }
    }
}
