<?php

namespace App\Http\Controllers\Api;

use App\Models\Style;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StyleController extends Controller
{
    use ApiResponse; // Use the trait

    // List all styles
    public function index()
    {
        $styles = Style::select(['id','name'])->where('status', 'active')->get();

        if ($styles->isEmpty()) {
            return $this->error([], 'Styles not found', 404);
        }

        return $this->success($styles, 'Styles retrieved successfully');
    }

    // Store a new style
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        try {
            // Retrieve validated data from the request
            $validatedData = $request->only(['name']);

            // Create the Style object with validated data
            $style = Style::create($validatedData);

            return $this->success($style, 'Style created successfully', 201); // Return success response
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500); // Return error response for unexpected errors
        }
    }

    // // Show a specific style
    // public function show($id)
    // {
    //     $style = Style::find($id);

    //     if (!$style) {
    //         return $this->error(null, 'Style not found', 404);
    //     }

    //     return $this->success($style, 'Style retrieved successfully');
    // }

    // // Update a style
    // public function update(Request $request, $id)
    // {
    //     $style = Style::find($id);

    //     if (!$style) {
    //         return $this->error(null, 'Style not found', 404);
    //     }

    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'status' => 'required|in:active,inactive',
    //     ]);

    //     $style->update($validatedData);

    //     return $this->success($style, 'Style updated successfully');
    // }

    // // Delete a style
    // public function destroy($id)
    // {
    //     $style = Style::find($id);

    //     if (!$style) {
    //         return $this->error(null, 'Style not found', 404);
    //     }

    //     $style->delete();

    //     return $this->success(null, 'Style deleted successfully');
    // }
}
