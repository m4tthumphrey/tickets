<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessCodeController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $code = strtoupper(trim($request->input('code')));

        $accessCode = AccessCode::where('code', $code)->first();

        if (! $accessCode) {
            return response()->json(['error' => 'Code not found.'], 422);
        }

        if (! $accessCode->isValid()) {
            return response()->json(['error' => $accessCode->invalidReason()], 422);
        }

        $accessCode->activate();

        // Re-check validity after activation (shouldn't fail, but be safe)
        if (! $accessCode->isValid()) {
            return response()->json(['error' => $accessCode->invalidReason()], 422);
        }

        $request->session()->put('access_code_id', $accessCode->id);

        return response()->json([
            'valid' => true,
            'expires_at' => $accessCode->expires_at?->toIso8601String(),
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $id = $request->session()->get('access_code_id');

        if (! $id) {
            return response()->json(['authenticated' => false]);
        }

        $accessCode = AccessCode::find($id);

        if (! $accessCode || ! $accessCode->isValid()) {
            $request->session()->forget('access_code_id');

            return response()->json(['authenticated' => false]);
        }

        return response()->json([
            'authenticated' => true,
            'expires_at' => $accessCode->expires_at?->toIso8601String(),
            'default_filters' => $accessCode->default_filters,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->forget('access_code_id');

        return response()->json(['success' => true]);
    }
}
