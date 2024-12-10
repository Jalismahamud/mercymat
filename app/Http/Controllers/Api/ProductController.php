<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Favorite;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponse;

    // // Fetch all products
    // public function index()
    // {
    //     $products = Product::with(['themes', 'styles'])->where('status', 'active')->get();

    //     if ($products->isEmpty()) {
    //         return $this->error([], 'Products not found', 404);
    //     }

    //     // Check if the user is authenticated using the 'api' guard
    //     $user = auth('api')->user();

    //     $products->each(function ($product) use ($user) {
    //         if ($user) {
    //             $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
    //         } else {
    //             $product->fav = false;
    //         }
    //     });

    //     return $this->success($products, 'Products retrieved successfully.');
    // }

    public function index()
    {
        $user = auth('api')->user();

        // Fetch all products with themes, styles, and the 'fav' status if a user is authenticated
        $products = Product::with(['themes', 'styles'])
            ->select('products.*')
            ->when($user, function ($query) use ($user) {
                $query->selectRaw(
                    'IF(favorites.id IS NOT NULL, true, false) as fav'
                )->leftJoin('favorites', function ($join) use ($user) {
                    $join->on('products.id', '=', 'favorites.product_id')
                        ->where('favorites.user_id', $user->id);
                });
            }, function ($query) {
                // Add 'fav' as false when user is not authenticated
                $query->selectRaw('false as fav');
            })
            ->where('status', 'active')
            ->get();

        if ($products->isEmpty()) {
            return $this->error([], 'Products not found', 404);
        }

        return $this->success($products, 'Products retrieved successfully.');
    }

    // Store a new product
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:free,premium',
            'image_url' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'nullable|numeric',
            'popular' => 'boolean',
            'description' => 'nullable|string',
            'theme_ids' => 'nullable|array', // IDs of themes
            'theme_ids.*' => 'exists:themes,id', // Ensure each theme ID exists in the themes table
            'style_ids' => 'nullable|array', // IDs of styles
            'style_ids.*' => 'exists:styles,id', // Ensure each style ID exists in the styles table
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {

            $validatedData = $validator->validated();

            if ($request->hasFile('image_url')) {
                $image  = $request->file('image_url');
                $imagePath = uploadImage($image, 'api/products');
                $validatedData['image_url'] = $imagePath;
            }

            $product = Product::create($validatedData);

            // Attach themes and styles
            $product->themes()->attach($request->theme_ids);
            $product->styles()->attach($request->style_ids);

            return $this->success($product, 'Product created successfully.');
        } catch (\Exception $e) {

            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // Find the product by its ID
        $product = Product::with(['themes', 'styles'])->select(['name', 'image_url'])->find($id);

        // Check if product exists
        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        // Check if the user is authenticated using the 'api' guard
        $user = auth('api')->user();

        // Add the 'fav' flag for the product based on the user's authentication status
        if ($user) {
            // Set 'fav' to true or false based on whether the product is a favorite
            $product->fav = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists();
        } else {
            // Set 'fav' to false if the user is not authenticated
            $product->fav = false;
        }

        // Return the product details
        return $this->success($product, 'Product retrieved successfully');
    }

    // // Update an existing product
    // public function update(Request $request, Product $product)
    // {
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'type' => 'required|in:free,premium',
    //         'price' => 'nullable|numeric',
    //         'status' => 'required|in:active,inactive',
    //         'popular' => 'boolean',
    //         'description' => 'nullable|string',
    //         'theme_ids' => 'array',
    //         'theme_ids.*' => 'exists:themes,id',
    //         'style_ids' => 'array',
    //         'style_ids.*' => 'exists:styles,id',
    //     ]);

    //     if ($request->hasFile('image')) {
    //         $image  = $request->file('image');
    //         $imagePath = uploadImage($image, 'api/products');
    //         $validatedData['image'] = $imagePath;
    //     }

    //     $product->update($validatedData);

    //     // Sync themes and styles
    //     $product->themes()->sync($request->theme_ids);
    //     $product->styles()->sync($request->style_ids);

    //     return $this->success($product, 'Product updated successfully.');
    // }

    // // Delete a product
    // public function destroy(Product $product)
    // {
    //     $product->delete();
    //     return $this->success(null, 'Product deleted successfully.');
    // }

}
