<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VendorSettingsController extends Controller
{
    /**
     * VENDOR ACTION: Resign from being a service provider.
     */
    public function resign(Request $request)
    {
        $user = $request->user();
        
        // Prevent normal tourists from hitting this
        abort_if($user->role === 'tourist' || $user->role === 'admin', 400, 'Only vendors can resign.');

        DB::transaction(function () use ($user) {
            // 1. Delete associated polymorphic images from storage & database
            // (In a massive production app you'd dispatch a background job for file cleanup, 
            // but for this scale, doing it synchronously is fine).
            $this->cleanupVendorImages($user);

            // 2. Delete the actual vendor profiles. 
            // Because of our migrations, cascading deletes will handle pivot tables and shop items.
            $user->guide()->delete();
            $user->hotels()->delete();
            $user->vehicles()->delete();
            $user->shops()->delete();

            // 3. Delete any pending role requests they might have
            $user->roleRequests()->delete();

            // 4. Revert role to tourist
            $user->update(['role' => 'tourist']);
        });

        return response()->json([
            'message' => 'You have successfully resigned. Your vendor profiles have been deleted, and your account is now a standard tourist account.'
        ]);
    }

    private function cleanupVendorImages($user)
    {
        // Helper to delete physical files and DB records for images owned by the user's entities
        $entities = collect()
            ->merge($user->hotels)
            ->merge($user->vehicles)
            ->merge($user->shops)
            ->push($user->guide)
            ->filter();

        foreach ($entities as $entity) {
            foreach ($entity->images as $image) {
                // Remove from local storage (e.g., /storage/vendor_images/xyz.jpg -> vendor_images/xyz.jpg)
                $path = str_replace('/storage/', '', $image->image_url);
                Storage::disk('public')->delete($path);
                $image->delete();
            }
        }
    }
}