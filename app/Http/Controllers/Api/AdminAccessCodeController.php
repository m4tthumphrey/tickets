<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Competition;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminAccessCodeController extends Controller
{
    public function index(): JsonResponse
    {
        $accessCodes = AccessCode::with('teams:id,name')
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json($accessCodes);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:access_codes,code',
            'label' => 'nullable|string|max:255',
            'expires_after' => 'nullable|integer|min:1',
            'margin' => 'integer|min:0|max:100',
            'default_filters' => 'nullable|array',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $accessCode = AccessCode::create([
            'code' => strtoupper(trim($request->input('code'))),
            'label' => $request->input('label'),
            'expires_after' => $request->input('expires_after'),
            'margin' => $request->input('margin', 15),
            'default_filters' => $request->input('default_filters'),
        ]);

        if ($request->filled('team_ids')) {
            $accessCode->teams()->sync($request->input('team_ids'));
        }

        $accessCode->load('teams:id,name');

        return response()->json($accessCode, 201);
    }

    public function show(AccessCode $accessCode): JsonResponse
    {
        $accessCode->load('teams:id,name');

        $logs = $accessCode->logs()
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json([
            'access_code' => $accessCode,
            'logs' => $logs,
        ]);
    }

    public function update(Request $request, AccessCode $accessCode): JsonResponse
    {
        $request->validate([
            'code' => 'sometimes|string|max:20|unique:access_codes,code,' . $accessCode->id,
            'label' => 'nullable|string|max:255',
            'expires_after' => 'nullable|integer|min:1',
            'margin' => 'integer|min:0|max:100',
            'default_filters' => 'nullable|array',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $data = $request->only(['code', 'label', 'expires_after', 'margin', 'default_filters']);

        if (array_key_exists('expires_after', $data) && $data['expires_after'] === null) {
            $data['expires_at'] = null;
        } elseif (array_key_exists('expires_after', $data) && $accessCode->activated_at) {
            $data['expires_at'] = $accessCode->activated_at->addMinutes($data['expires_after']);
        }

        $accessCode->update($data);

        if ($request->has('team_ids')) {
            $accessCode->teams()->sync($request->input('team_ids', []));
        }

        $accessCode->load('teams:id,name');

        return response()->json($accessCode);
    }

    public function revoke(AccessCode $accessCode): JsonResponse
    {
        $accessCode->update(['is_revoked' => true]);

        return response()->json($accessCode);
    }

    public function destroy(AccessCode $accessCode): JsonResponse
    {
        $accessCode->delete();

        return response()->json(['success' => true]);
    }

    public function filters(): JsonResponse
    {
        $teams = Team::whereIn('id',
            Product::whereNotNull('home_team_id')->distinct()->pluck('home_team_id')
        )->orderBy('name')->get(['id', 'name']);
        $competitions = Competition::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'teams' => $teams,
            'competitions' => $competitions,
        ]);
    }
}
