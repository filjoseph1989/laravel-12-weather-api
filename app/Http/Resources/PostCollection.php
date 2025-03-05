<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->collection->count() > 0 ? 'Posts retrieved successfully.' : 'No posts found.',
            'data' => PostResource::collection($this->collection),
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            "meta" => [
                "current_page" => $this->currentPage(),
                "from" => $this->firstItem(),
                "to" => $this->lastItem(),
                "last_page" => $this->lastPage(),
                "path" => $this->url(1),
                "per_page" => $this->perPage(),
                "total" => $this->total(),
            ]
        ];
    }
}
