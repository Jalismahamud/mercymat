<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Style;
use App\Models\Theme;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SearchConroller extends Controller
{
    use ApiResponse;

    /**
         * Search for active products by name, themes, or styles.
         *
         * @param  \Illuminate\Http\Request  $request  The HTTP request with the search query.
         * @return \Illuminate\Http\JsonResponse  JSON response with products or error.
    */

    public function searchAllProduct(Request $request) {

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $search = $request->input('search');


        $data = Product::where(function ($query) use ($search) {
            // Search in the products table

            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('themes', function ($query) use ($search) {
                      // Search in the related themes table
                      $query->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('styles', function ($query) use ($search) {
                      // Search in the related styles table
                      $query->where('name', 'like', '%' . $search . '%');
                  });
        })
        ->where('status', 'active')
        ->latest() // Order by the latest created
        ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Products not found', 404);
        }

        return $this->success($data, 'Product fetched successfully', '200');
    }

    /**
         * Search active & Premiun products.
         *
         * @param  \Illuminate\Http\Request  $request  Search query in the HTTP request.
         * @return \Illuminate\Http\JsonResponse  JSON response with products or error.
    */

    public function searchPremiumProduct(Request $request) {

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $search = $request->input('search');

        $data = Product::query()
                ->where('name', 'like', '%' . $search . '%')
                ->where('status', 'active')
                ->where('type', 'premium')
                ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Premium Products not found', 404);
        }

        return $this->success($data, 'Premium Product fetched successfully', '200');
    }

    /**
         * Search active & favorite products.
         *
         * @param  \Illuminate\Http\Request  $request  Search query in the HTTP request.
         * @return \Illuminate\Http\JsonResponse  JSON response with products or error.
    */

    public function searchFavoriteProduct(Request $request) {

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $search = $request->input('search');

        $user = Auth::user();

        if (!$user) {
            return $this->error([], 'User not authenticated', 401);
        }

        $data = $user->favoriteProducts()
                ->where('name', 'like', '%' . $search . '%')
                ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Favorite Products not found', 404);
        }

        return $this->success($data, 'Favorite Product fetched successfully', '200');
    }
}
