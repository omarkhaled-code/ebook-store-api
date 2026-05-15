<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\Order;
use App\Services\DownloadService;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    private DownloadService $downloadService;

    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    // POST /api/v1/downloads/generate/{order_id}
    // Generate a download token for a paid order
    public function generate(int $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->with('ebook')
            ->firstOrFail();

        try {
            $download = $this->downloadService->generateToken(
                $order,
                auth()->id()
            );

            return response()->json([
                'message'      => 'Download link generated.',
                'download_url' => url("api/v1/downloads/{$download->token}"),
                'expires_at'   => $download->expires_at,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    // GET /api/v1/downloads/{token}
    // Stream the PDF file
    public function stream(string $token)
    {
        // Find the token
        $download = Download::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Make sure the authenticated user owns this token
        if ($download->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        // Get the ebook
        $ebook = $download->order->ebook;

        // Check the file exists
        $path = Storage::disk('local')->path($ebook->pdf_path);

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        // Mark as downloaded
        $download->update(['downloaded_at' => now()]);

        // Stream the PDF — never expose the real path
        return response()->streamDownload(function () use ($path) {
            $stream = fopen($path, 'r');
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        }, $ebook->title . '.pdf', [
            'Content-Type'  => 'application/pdf',
            'Cache-Control' => 'no-store, no-cache',
            'Pragma'        => 'no-cache',
        ]);
    }
}