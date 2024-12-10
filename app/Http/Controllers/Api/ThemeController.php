<?php

namespace App\Http\Controllers\Api;

use App\Models\Theme;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    use ApiResponse;

    // Fetch all themes
    public function index()
    {
        $themes = Theme::select(['id', 'name', 'image_url'])->where('status', 'active')->get();

        if ($themes->isEmpty()) {
            return $this->error([], "Themes not found", 404);
        }

        return $this->success($themes, 'Themes fetched successfully', 200);
    }

    // Store a new theme
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {

            $validatedData = $request->only(['name']);
            if ($request->hasFile('image_url')) {

                $image  = $request->file('image_url');

                $imagePath = uploadImage($image, 'api/themes');

                $validatedData['image_url'] = $imagePath;
            }

            $theme = Theme::create($validatedData);

            return $this->success($theme, 'Theme created successfully', 200);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    // // Show a single theme
    // public function show($id)
    // {
    //     $theme = Theme::find($id);

    //     if (!$theme) {
    //         return $this->error(null, 'Theme not found', 404);
    //     }

    //     return $this->success($theme, 'Theme fetched successfully');
    // }

    // // Update an existing theme
    // public function update(Request $request, $id)
    // {
    //     $theme = Theme::find($id);

    //     if (!$theme) {
    //         return $this->error(null, 'Theme not found', 404);
    //     }

    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'image' => 'nullable|string',
    //         'status' => 'required|in:active,inactive',
    //     ]);

    //     $theme->update($validatedData);

    //     return $this->success($theme, 'Theme updated successfully');
    // }

    // // Delete a theme
    // public function destroy($id)
    // {
    //     $theme = Theme::find($id);

    //     if (!$theme) {
    //         return $this->error(null, 'Theme not found', 404);
    //     }

    //     $theme->delete();

    //     return $this->success(null, 'Theme deleted successfully');
    // }

    // public function getProductsByTheme($themeId)
    // {
    //     // Fetch the theme by ID and load its products
    //     $theme = Theme::with('products')->find($themeId);

    //     if (!$theme) {
    //         return $this->error([], 'Theme not found', 404);
    //     }

    //     // Fetch all products associated with the theme
    //     $products = $theme->products;

    //     return $this->success($products, 'Products retrieved successfully for the specified theme.');
    // }

    // // Method to get popular products under themes
    // public function getPopularProducts()
    // {
    //     $popularProducts = Product::where('popular', true)
    //         ->select(['id', 'name', 'image_url','type', 'description', 'price'])
    //         ->get();


    //     if (!$popularProducts) {
    //         return $this->error([], 'Popular products not found', 404);
    //     }

    //     return $this->success($popularProducts, 'Popular products retrieved successfully');
    // }

    // // Method to get popular products under themes
    // public function getPremiumProducts()
    // {
    //     $premiumProducts = Product::where('type', 'premium')
    //         ->select(['id', 'name', 'image_url','type', 'description', 'price'])
    //         ->get();

    //     if ($premiumProducts->isEmpty()) {
    //         return $this->error([], 'Premium products not found', 404);
    //     }

    //     return $this->success($premiumProducts, 'Premium products retrieved successfully');
    // }

    // Get products by theme
    public function getProductsByTheme($themeId)
    {
        // Fetch the theme by ID and load its products
        $theme = Theme::with('products')->find($themeId);

        if (!$theme) {
            return $this->error([], 'Theme not found', 404);
        }

        $products = $theme->products;

        // Check if the user is authenticated using the 'api' guard
        $user = auth('api')->user();

        // if ($user) {
        //     // Add the 'is_favorite' flag for each product if authenticated
        //     $products->each(function ($product) use ($user) {
        //         $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        //     });
        // }

        // if ($user == null) {
        // } else {

        //     // Add the 'is_favorite' flag for each product if authenticated
        //     $products->each(function ($product) use ($user) {
        //         $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        //     });
        // }

        // Add the 'fav' flag for each product based on the user's authentication status
        $products->each(function ($product) use ($user) {
            if ($user) {
                // Set 'fav' to true or false based on the user's favorites
                $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
            } else {
                // Set 'fav' to false if the user is not authenticated
                $product->fav = false;
            }
        });

        return $this->success($products, 'Products retrieved successfully for the specified theme.');
    }

    // // Get popular products
    // public function getPopularProducts()
    // {
    //     $popularProducts = Product::where('popular', true)
    //         ->select(['id', 'name', 'image_url', 'type', 'description', 'price'])
    //         ->get();

    //     if ($popularProducts->isEmpty()) {
    //         return $this->error([], 'Popular products not found', 404);
    //     }

    //     // Check if the user is authenticated using the 'api' guard
    //     $user = auth('api')->user();

    //     if ($user) {
    //         // Add the 'is_favorite' flag for each product if authenticated
    //         $popularProducts->each(function ($product) use ($user) {
    //             $product->is_favorite = $user->favorites()->where('product_id', $product->id)->exists();
    //         });
    //     }

    //     return $this->success($popularProducts, 'Popular products retrieved successfully');
    // }

    public function getPopularProducts()
    {
        $popularProducts = Product::where('popular', true)
            ->select(['id', 'name', 'image_url', 'type', 'description', 'price'])
            ->get();

        if ($popularProducts->isEmpty()) {
            return $this->error([], 'Popular products not found', 404);
        }

        // Check if the user is authenticated using the 'api' guard
        $user = auth('api')->user();



        // if ($user == null) {
        // } else {

        //     // Add the 'is_favorite' flag for each product if authenticated
        //     $popularProducts->each(function ($product) use ($user) {
        //         $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        //     });
        // }

        // Add the 'fav' flag for each product based on the user's authentication status
        $popularProducts->each(function ($product) use ($user) {
            if ($user) {
                $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
            } else {
                $product->fav = false;
            }
        });

        return $this->success($popularProducts, 'Popular products retrieved successfully');
    }

    // Get premium products
    public function getPremiumProducts()
    {
        $premiumProducts = Product::where('type', 'premium')
            ->select(['id', 'name', 'image_url', 'type', 'description', 'price'])
            ->get();

        if ($premiumProducts->isEmpty()) {
            return $this->error([], 'Premium products not found', 404);
        }

        // Check if the user is authenticated using the 'api' guard
        $user = auth('api')->user();

        // if ($user) {
        //     // Add the 'is_favorite' flag for each product if authenticated
        //     $premiumProducts->each(function ($product) use ($user) {
        //         $product->is_favorite = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        //     });
        // }

        // if ($user == null) {
        // } else {

        //     // Add the 'is_favorite' flag for each product if authenticated
        //     $premiumProducts->each(function ($product) use ($user) {
        //         $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        //     });
        // }

        // Add the 'fav' flag for each product based on the user's authentication status
        $premiumProducts->each(function ($product) use ($user) {
            if ($user) {
                $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
            } else {
                $product->fav = false;
            }
        });

        return $this->success($premiumProducts, 'Premium products retrieved successfully');
    }
}
