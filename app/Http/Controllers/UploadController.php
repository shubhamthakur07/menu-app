<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Handle image upload from computer / phone.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120', // Max 5MB
            'folder' => 'nullable|string|in:dishes,logos,covers,general',
        ]);

        $folder = $request->input('folder', 'dishes');
        $file = $request->file('image');

        // Clean filename with timestamp
        $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs("uploads/{$folder}", $filename, 'public');

        // Return the accessible public URL
        $url = asset('storage/'.$path);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully!',
            'url' => $url,
            'path' => $path,
        ]);
    }
}
