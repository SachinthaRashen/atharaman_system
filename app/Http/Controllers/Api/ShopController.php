<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * VENDOR ACTION: Create Shop
     */
    public function store(Request $request)
    {
        abort_if($request->user()->role !== 'shop_owner', 403, 'Only verified shop owners can register a shop.');

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'store_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $shop = new Shop();
        $shop->fill($request->except(['latitude', 'longitude', 'store_image']));
        $shop->user_id = $request->user()->id;

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $shop->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        $shop->save();

        if ($request->hasFile('store_image')) {
            $path = $request->file('store_image')->store('vendor_images', 'public');
            $shop->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => 'Shop registered successfully!',
            'shop_id' => $shop->id
        ], 201);
    }

    /**
     * VENDOR ACTION: Update Shop Details & Storefront Image
     */
    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        abort_if($shop->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $validated = $request->validate([
            'shop_name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'contact_number' => 'sometimes|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'store_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $shop->fill($request->except(['latitude', 'longitude', 'store_image']));

        if (isset($validated['latitude']) && isset($validated['longitude'])) {
            $lat = (float) $validated['latitude'];
            $lng = (float) $validated['longitude'];
            $shop->coordinates = DB::raw("ST_SetSRID(ST_MakePoint({$lng}, {$lat}), 4326)::geography");
        }

        $shop->save();

        if ($request->hasFile('store_image')) {
            // Delete old storefront images
            foreach ($shop->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }

            $path = $request->file('store_image')->store('vendor_images', 'public');
            $shop->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json(['message' => 'Shop updated successfully!', 'shop' => $shop]);
    }

    /**
     * VENDOR ACTION: Delete Shop (Cascades items and removes images)
     */
    public function destroy(Request $request, $id)
    {
        $shop = Shop::with('items.images')->findOrFail($id);
        abort_if($shop->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        // 1. Delete Shop Storefront Images
        foreach ($shop->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        // 2. Delete All Shop Items' Images
        foreach ($shop->items as $item) {
            foreach ($item->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }
        }

        $shop->delete();

        return response()->json(['message' => 'Shop and all associated items deleted successfully.']);
    }

    // =========================================================================
    // SHOP ITEMS MANAGEMENT (Strictly Vendor-Only)
    // =========================================================================

    /**
     * VENDOR ACTION: Add Item to Shop
     */
    public function storeItem(Request $request, $shopId)
    {
        $shop = Shop::findOrFail($shopId);
        abort_if($shop->user_id !== $request->user()->id, 403, 'Unauthorized access.');

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_category' => 'required|in:camping_gear,hiking_trekking,water_sports,general_travel',
            'description' => 'nullable|string',
            'rental_price_per_day' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'item_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = new ShopItem($request->except(['item_image']));
        $item->shop_id = $shop->id;
        $item->save();

        if ($request->hasFile('item_image')) {
            $path = $request->file('item_image')->store('vendor_images', 'public');
            $item->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => 'Item added to shop successfully!',
            'item' => $item->load('images')
        ], 201);
    }

    /**
     * VENDOR ACTION: Update Shop Item Details & Image
     */
    public function updateItem(Request $request, $shopId, $itemId)
    {
        $shop = Shop::findOrFail($shopId);
        abort_if($shop->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $item = ShopItem::where('shop_id', $shop->id)->findOrFail($itemId);

        $validated = $request->validate([
            'item_name' => 'sometimes|string|max:255',
            'item_category' => 'sometimes|in:camping_gear,hiking_trekking,water_sports,general_travel',
            'description' => 'nullable|string',
            'rental_price_per_day' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:1',
            'item_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item->update($request->except(['item_image']));

        if ($request->hasFile('item_image')) {
            foreach ($item->images as $image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
                $image->delete();
            }

            $path = $request->file('item_image')->store('vendor_images', 'public');
            $item->images()->create([
                'image_url' => '/storage/' . $path,
                'is_primary' => true,
            ]);
        }

        return response()->json(['message' => 'Shop item updated successfully!', 'item' => $item->load('images')]);
    }

    /**
     * VENDOR ACTION: Delete a Single Shop Item
     */
    public function destroyItem(Request $request, $shopId, $itemId)
    {
        $shop = Shop::findOrFail($shopId);
        abort_if($shop->user_id !== $request->user()->id && $request->user()->role !== 'admin', 403, 'Unauthorized access.');

        $item = ShopItem::where('shop_id', $shop->id)->findOrFail($itemId);

        foreach ($item->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
            $image->delete();
        }

        $item->delete();

        return response()->json(['message' => 'Shop item deleted successfully.']);
    }
}