<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Product;

class ProductApiTest extends TestCase
{
    use DatabaseTransactions;

    public function testItCanListProducts()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function testItCanCreateAProduct()
    {
        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100,
            'quantity' => 5
        ];

        $response = $this->postJson('/api/v1/products', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Test Product']);
        
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    public function testCreateProductValidationFails()
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => '',
            'price' => null
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'price']);
    }

    public function testProductCreationFailsWithoutName()
    {
        $payload = [
            'price' => 50,
            'description' => 'No name here'
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function testProductCreationFailsWithNegativePrice()
    {
        $payload = [
            'name' => 'Invalid Product',
            'price' => -10,
            'quantity' => 5,
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);
    }

    public function testItCanUpdateAProduct()
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 200,
            'quantity' => 10
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Product']);

        $this->assertDatabaseHas('products', ['name' => 'Updated Product']);
    }

    public function testUpdateNonExistentProductReturnsNotFound()
    {
        $response = $this->putJson('/api/v1/products/9999', [
            'name' => 'Invalid',
            'price' => 50,
            'quantity' => 5
        ]);

        $response->assertStatus(404);
    }

    public function testItCanDeleteAProduct()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function testDeleteNonExistentProductReturnsNotFound()
    {
        $response = $this->deleteJson('/api/v1/products/9999');

        $response->assertStatus(404);
    }
}
