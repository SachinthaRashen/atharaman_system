<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoleRequestController extends Controller
{
    /**
     * TOURIST ACTION: Submit a new role request.
     */
    public function submitRequest(Request $request)
    {
        $user = $request->user();

        // 1. Prevent spam: Check if they already have a pending request
        $existingRequest = RoleRequest::where('user_id', $user->id)
                                      ->where('status', 'pending')
                                      ->first();
        if ($existingRequest) {
            return response()->json(['message' => 'You already have a pending role request.'], 422);
        }

        // 2. Validate input
        $validated = $request->validate([
            'request_type' => 'required|in:guide,hotel_owner,shop_owner,vehicle_owner',
            'business_name' => 'nullable|string|max:255',
            'contact_number' => 'required|string|max:20',
            'credentials_description' => 'required|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB Max
        ]);

        // 3. Handle File Upload locally
        $documentPath = null;
        if ($request->hasFile('document')) {
            // Stores in storage/app/public/vendor_documents
            $documentPath = $request->file('document')->store('vendor_documents', 'public');
        }

        // 4. Create the request
        $roleRequest = RoleRequest::create([
            'user_id' => $user->id,
            'request_type' => $validated['request_type'],
            'business_name' => $validated['business_name'],
            'contact_number' => $validated['contact_number'],
            'credentials_description' => $validated['credentials_description'],
            'document_url' => $documentPath ? '/storage/' . $documentPath : null,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Role request submitted successfully.',
            'data' => $roleRequest
        ], 201);
    }

    /**
     * ADMIN ACTION: View all pending requests.
     */
    public function index(Request $request)
    {
        // Simple authorization: only admins can view this
        abort_if($request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $requests = RoleRequest::with('user:id,name,email')
                               ->where('status', 'pending')
                               ->orderBy('created_at', 'asc')
                               ->get();

        return response()->json($requests);
    }

    /**
     * ADMIN ACTION: Approve or Decline a request.
     */
    public function reviewRequest(Request $request, $id)
    {
        // Simple authorization
        abort_if($request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $validated = $request->validate([
            'status' => 'required|in:approved,declined',
            'admin_notes' => 'nullable|string'
        ]);

        $roleRequest = RoleRequest::findOrFail($id);

        if ($roleRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed.'], 400);
        }

        // Use a DB Transaction to ensure both tables update perfectly, or neither do
        DB::transaction(function () use ($roleRequest, $validated) {
            
            // 1. Update the request status
            $roleRequest->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            // 2. If approved, upgrade the user's role
            if ($validated['status'] === 'approved') {
                $roleRequest->user->update([
                    'role' => $roleRequest->request_type
                ]);
            }
        });

        return response()->json([
            'message' => 'Role request ' . $validated['status'] . ' successfully.',
            'new_user_role' => $roleRequest->user->role
        ]);
    }
}