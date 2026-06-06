<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EbookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'author' => $this->author,
            'cover_image_path' => $this->cover_image_path,
            'price' => $this->price,
            'is_published' => $this->when($request->user()?->isAdmin(), $this->is_published),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
