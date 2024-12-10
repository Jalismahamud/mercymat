<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    use ApiResponse;

    // Add or Remove product from favorites
    public function toggleFavorite(Request $request)
    {
        $user = auth()->user();
        $productId = $request->input('product_id');

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        // Check if the product is already in favorites
        $favorite = Favorite::where('user_id', $user->id)->where('product_id', $productId)->first();

        if ($favorite) {
            // If exists, remove from favorites
            $favorite->delete();
            return $this->success([], 'Product removed from favorites.');
        } else {
            // If not, add to favorites
            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            return $this->success([], 'Product added to favorites.');
        }
    }

    public function getFavorites()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not authenticated', 401);
        }

        // Fetch favorite products using the relationship
        $favorites = $user->favorites;

        if ($favorites->isEmpty()) {
            return $this->error([], 'No favorite products found', 404);
        }

        return $this->success($favorites, 'Favorite products retrieved successfully.');
    }
}
