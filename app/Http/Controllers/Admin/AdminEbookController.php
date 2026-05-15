<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminEbookController extends Controller
{
    // GET /api/v1/admin/ebooks — list all ebooks (including drafts)
    public function index()
    {
        $ebooks = Ebook::latest()->paginate(12);

        return response()->json($ebooks);
    }

    // POST /api/v1/admin/ebooks — upload new ebook
    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'author'      => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'pdf'         => ['required', 'mimes:pdf', 'max:51200'],
        ]);

        // Handle cover image upload
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')
                ->store('covers', 'public');
        }

        // Handle PDF upload — stored privately
        $pdfPath = $request->file('pdf')
            ->store('ebooks', 'local');

        // Create the ebook
        $ebook = Ebook::create([
            'title'            => $request->title,
            'slug'             => Str::slug($request->title) . '-' . uniqid(),
            'description'      => $request->description,
            'author'           => $request->author,
            'price'            => $request->price,
            'price_in_cents'   => $request->price * 100,
            'cover_image_path' => $coverPath,
            'pdf_path'         => $pdfPath,
            'is_published'     => $request->boolean('is_published', false),
        ]);

        return response()->json([
            'message' => 'Ebook created successfully.',
            'data'    => $ebook,
        ], 201);
    }

    // PUT /api/v1/admin/ebooks/{id} — update ebook
    public function update(Request $request, Ebook $ebook)
    {
        $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'author'      => ['sometimes', 'string', 'max:255'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'is_published'=> ['sometimes', 'boolean'],
        ]);

        // Update slug if title changed
        if ($request->has('title')) {
            $ebook->slug = Str::slug($request->title) . '-' . uniqid();
        }

        // Update price_in_cents if price changed
        if ($request->has('price')) {
            $ebook->price_in_cents = $request->price * 100;
        }

        $ebook->update($request->only([
            'title',
            'description',
            'author',
            'price',
            'is_published',
        ]));

        return response()->json([
            'message' => 'Ebook updated successfully.',
            'data'    => $ebook,
        ]);
    }

    // DELETE /api/v1/admin/ebooks/{id} — delete ebook
    public function destroy(Ebook $ebook)
    {
        // Delete PDF from private storage
        Storage::disk('local')->delete($ebook->pdf_path);

        // Delete cover from public storage
        if ($ebook->cover_image_path) {
            Storage::disk('public')->delete($ebook->cover_image_path);
        }

        $ebook->delete();

        return response()->json([
            'message' => 'Ebook deleted successfully.',
        ]);
    }

    // GET /api/v1/admin/ebooks/{id}
    public function show(Ebook $ebook)
    {
        return response()->json([
            'data' => $ebook
        ]);
    }
}