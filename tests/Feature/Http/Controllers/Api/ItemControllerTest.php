<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\Item;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function collection_request_returns_a_successful_response(): void
    {
        $items = Item::factory(3)->create();
        $response = $this->getJson('/api/items');

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('meta')
                    ->has('links')
                    ->has('data', 3)
                    ->has(
                        'data.0',
                        fn ($json) =>
                        $json
                            ->where('id', $items->first()->id)
                            ->where('name', $items->first()->name)
                            ->where('description', $items->first()->description)
                            ->where('completed', (int)$items->first()->completed)
                            ->where('created_at', $items->first()->created_at->toISOString())
                            ->where('updated_at', $items->first()->updated_at->toISOString())
                        // ->etc()
                    )
            );
    }

    /**
     * @test
     */
    public function collection_has_default_max_limit(): void
    {
        $items = Item::factory(50)->create();
        $response = $this->getJson('/api/items');

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data', 25)
                    ->where('meta.per_page', 25)
                    ->etc()
            );
    }

    /**
     * @test
     */
    public function collection_can_be_filter_by_name(): void
    {
        $items = Item::factory(3)->create([
            'name' => 'name'
        ]);
        $items = Item::factory(3)->create([
            'name' => 'test name'
        ]);
        $response = $this->getJson('/api/items?name=test');

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data', 3)
                    ->etc()
            );
    }

    /**
     * @test
     */
    public function collection_can_be_filter_by_completed(): void
    {
        $items = Item::factory(3)->create([
            'completed' => 0
        ]);
        $items = Item::factory(3)->create([
            'completed' => 1
        ]);
        $response = $this->getJson('/api/items?completed=0');

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data', 3)
                    ->etc()
            );
    }

    /**
     * @test
     * @dataProvider badCompletedParameterCases
     */
    public function it_throws_error_when_collection_completed_parameter_is_invalid(
        string|int $completedParameter
    ): void {
        $items = Item::factory(3)->create();
        $response = $this->getJson("/api/items?completed={$completedParameter}");

        $response
            ->assertStatus(422);
    }

    public function badCompletedParameterCases(): array
    {
        return [
            ['str'],
            [2],
        ];
    }

    /**
     * @test
     */
    public function collection_per_page_can_be_set(): void
    {
        $items = Item::factory(50)->create();
        $response = $this->getJson('/api/items?per_page=15');

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('data', 15)
                    ->where('meta.per_page', 15)
                    ->etc()
            );
    }

    /**
     * @test
     */
    public function collection_page_can_be_set(): void
    {
        $items = Item::factory(50)->create();
        $response = $this->getJson('/api/items?page=2');

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('meta.current_page', 2)
                    ->etc()
            );
    }

    /**
     * @test
     * @dataProvider badPerPageParameterCases
     */
    public function it_throws_error_when_collection_per_page_parameter_is_invalid(
        string $pageParameter
    ): void {
        $items = Item::factory(3)->create();
        $response = $this->getJson("/api/items?per_page={$pageParameter}");

        $response
            ->assertStatus(422);
    }

    public function badPerPageParameterCases(): array
    {
        return [
            ['str'],
            ['0'],
            ['-1']
        ];
    }

    /**
     * @test
     * @dataProvider badPageParameterCases
     */
    public function it_throws_error_when_collection_page_parameter_is_invalid(
        string $pageParameter
    ): void {
        $items = Item::factory(3)->create();
        $response = $this->getJson("/api/items?page={$pageParameter}");

        $response
            ->assertStatus(422);
    }

    public function badPageParameterCases(): array
    {
        return [
            ['str'],
            ['0'],
            ['-1']
        ];
    }

    /**
     * @test
     */
    public function entity_request_returns_a_successful_response(): void
    {
        $items = Item::factory(50)->create();

        $response = $this->getJson('/api/items/1');

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('id', $items->first()->id)
                            ->where('name', $items->first()->name)
                            ->where('description', $items->first()->description)
                            ->where('completed', (int)$items->first()->completed)
                            ->where('created_at', $items->first()->created_at->toISOString())
                            ->where('updated_at', $items->first()->updated_at->toISOString())
                    )
            );
    }

    /**
     * @test
     */
    public function it_throws_error_when_entity_not_exists(): void
    {
        $response = $this->getJson('/api/items/1');

        $response
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function store_entity_successful(): void
    {
        $item = Item::factory()->make();
        $response = $this->postJson('/api/items', [
            'name' => $item->name,
            'description' => $item->description,
            'completed' => $item->completed,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('name', $item->name)
                            ->where('description', $item->description)
                            ->where('completed', $item->completed)
                            ->etc()
                    )
            );
    }

    /**
     * @test
     * @dataProvider badStoreParameterCases
     */
    public function it_throws_error_when_store_parameter_is_invalid(
        array $storeParameters
    ): void {
        $response = $this->postJson('/api/items', $storeParameters);

        $response
            ->assertStatus(422);
    }

    public function badStoreParameterCases(): array
    {
        return [
            [
                [
                    'name' => 'test name',
                    'completed' => 1,
                ],
            ],
            [
                [
                    'description' => 'test description',
                    'completed' => 1,
                ],
            ],
            [
                [
                    'name' => 'test name',
                    'description' => 'test description',
                ],
            ],
            [
                [
                    'name' => 'test name',
                    'description' => 'test description',
                    'completed' => 'bad value',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_update_entity(): void
    {
        $item = Item::factory()->create();
        $updateItem = Item::factory()->make();

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => $updateItem->name,
            'description' => $updateItem->description,
            'completed' => $updateItem->completed,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('name', $updateItem->name)
                            ->where('description', $updateItem->description)
                            ->where('completed', $updateItem->completed)
                            ->etc()
                    )
            );
    }

    /**
     * @test
     * @dataProvider badUpdateParameterCases
     */
    public function it_throws_error_when_update_parameter_is_invalid(
        array $updateParameters
    ): void {
        $item = Item::factory()->create();
        $response = $this->putJson("/api/items/{$item->id}", $updateParameters);

        $response
            ->assertStatus(422);
    }

    public function badUpdateParameterCases(): array
    {
        return [
            [
                [
                    'completed' => 'bad value',
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_delete_entity(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson("/api/items/{$item->id}");

        $response
            ->assertStatus(204);
    }

    /**
     * @test
     */
    public function it_throws_error_on_delete_when_entity_not_exists(): void
    {
        $response = $this->deleteJson("/api/items/1");

        $response
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_can_sanitize_name_on_store(): void
    {
        $item = Item::factory()->make([
            'name' => '<script>alert("Harmful Script");</script><p>Test</p>',
        ]);
        $response = $this->postJson('/api/items', [
            'name' => $item->name,
            'description' => $item->description,
            'completed' => $item->completed,
        ]);

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('name', '<p>Test</p>')
                            ->etc()
                    )
            );
    }

    /**
     * @test
     */
    public function it_can_sanitize_name_on_update(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => '<script>alert("Harmful Script");</script><p>Test</p>',
        ]);

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('name', '<p>Test</p>')
                            ->etc()
                    )
            );
    }

    /**
     * @test
     */
    public function it_can_sanitize_description_on_store(): void
    {
        $item = Item::factory()->make([
            'description' => '<script>alert("Harmful Script");</script><p>Test</p>',
        ]);
        $response = $this->postJson('/api/items', [
            'name' => $item->name,
            'description' => $item->description,
            'completed' => $item->completed,
        ]);

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('description', '<p>Test</p>')
                            ->etc()
                    )
            );
    }

    /**
     * @test
     */
    public function it_can_sanitize_description_on_update(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson("/api/items/{$item->id}", [
            'description' => '<script>alert("Harmful Script");</script><p>Test</p>',
        ]);

        $response
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'data',
                        fn ($json) =>
                        $json
                            ->where('description', '<p>Test</p>')
                            ->etc()
                    )
            );
    }
}
