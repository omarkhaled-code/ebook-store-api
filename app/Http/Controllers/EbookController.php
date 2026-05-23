<?php

namespace App\Http\Controllers;

use App\Models\Ebook;

class EbookController extends Controller
{
    // GET /api/v1/ebooks — list all published ebooks
    public function index()
    {
        $ebooks = Ebook::where('is_published', true)
            ->latest()
            ->paginate(12);

        return response()->json($ebooks);
    }

    // GET /api/v1/ebooks/{slug} — single ebook detail
    public function show(string $slug)
    {
        $ebook = Ebook::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json([
            'data' => $ebook
        ]);
    }
}