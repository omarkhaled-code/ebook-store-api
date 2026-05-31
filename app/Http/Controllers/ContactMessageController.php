<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\UpdateContactMessageRequest;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if(!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $messages = ContactMessage::latest()->get();
        return response()->json($messages, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactMessageRequest $request)
    {
        $message = ContactMessage::create($request->validated());

        return response()->json($message, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactMessage $contactMessage)
    {
        $user = auth()->user();
        if(!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $contactMessage->delete();

        return response()->json(null, 204);
    }
}
