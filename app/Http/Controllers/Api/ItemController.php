<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetItemsRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(GetItemsRequest $request): JsonResponse
    {
        return JsonResource::collection(
            Item::query()
                ->when(
                    $request->filled('name'),
                    fn ($builder) =>
                    $builder->where('name', 'like', "%{$request->get('name')}%")
                )
                ->when(
                    $request->filled('completed'),
                    fn ($builder) =>
                    $builder->where('completed', $request->get('completed'))
                )
                ->paginate($request->get('per_page') ?? 25)
        )->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreItemRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = Item::create($request->validated());

        return (new JsonResource($item))->response();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Item $item): JsonResponse
    {
        return (new JsonResource($item))->response();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateItemRequest  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        tap($item)->update($request->validated());

        return (new JsonResource($item))->response();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}
