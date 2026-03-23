<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

class ProductApiTest extends TestCase
{
    private Client $http;
    private string $base = 'http://localhost:8080/api';

    protected function setUp(): void
    {
        $this->http = new Client([
            'base_uri'    => 'http://localhost:8080',
            'http_errors' => false,   
            'headers'     => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        ]);
    }


    private function decode(\GuzzleHttp\Psr7\Response|\Psr\Http\Message\ResponseInterface $res): array
    {
        return json_decode((string) $res->getBody(), true);
    }

    private function createProduct(array $overrides = []): array
    {
        $payload = array_merge([
            'name'        => 'Test Product ' . uniqid(),
            'description' => 'A product created in tests.',
            'price'       => 49.99,
            'quantity'    => 10,
            'category'    => 'Testing',
        ], $overrides);

        $res  = $this->http->post('/api/products', ['json' => $payload]);
        $body = $this->decode($res);
        $this->assertEquals(201, $res->getStatusCode(), 'createProduct failed');
        return $body['data'];
    }


    public function test_ping_returns_200(): void
    {
        $res  = $this->http->get('/api/ping');
        $body = $this->decode($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($body['success']);
        $this->assertStringContainsString('running', $body['message']);
    }


    public function test_index_returns_200_with_paginated_data(): void
    {
        $res  = $this->http->get('/api/products');
        $body = $this->decode($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('total', $body['data']);
        $this->assertArrayHasKey('data', $body['data']);
    }

    public function test_index_search_filters_by_name(): void
    {
        $unique = 'UniqueXYZ' . uniqid();
        $this->createProduct(['name' => $unique]);

        $res  = $this->http->get("/api/products?search={$unique}");
        $body = $this->decode($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals(1, $body['data']['total']);
        $this->assertEquals($unique, $body['data']['data'][0]['name']);
    }

    public function test_index_filters_by_category(): void
    {
        $cat = 'CatTest' . uniqid();
        $this->createProduct(['category' => $cat]);
        $this->createProduct(['category' => $cat]);

        $res  = $this->http->get("/api/products?category={$cat}");
        $body = $this->decode($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals(2, $body['data']['total']);
    }


    public function test_store_creates_product_and_returns_201(): void
    {
        $res  = $this->http->post('/api/products', ['json' => [
            'name'     => 'New Gadget',
            'price'    => 99.00,
            'quantity' => 5,
            'category' => 'Gadgets',
        ]]);
        $body = $this->decode($res);

        $this->assertEquals(201, $res->getStatusCode());
        $this->assertTrue($body['success']);
        $this->assertEquals('New Gadget', $body['data']['name']);
        $this->assertArrayHasKey('id', $body['data']);
    }

    public function test_store_returns_422_when_required_fields_missing(): void
    {
        $res  = $this->http->post('/api/products', ['json' => []]);
        $body = $this->decode($res);

        $this->assertEquals(422, $res->getStatusCode());
        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('name', $body['errors']);
    }

    public function test_store_returns_422_for_negative_price(): void
    {
        $res  = $this->http->post('/api/products', ['json' => [
            'name' => 'Bad Product', 'price' => -10, 'quantity' => 1, 'category' => 'Test',
        ]]);
        $body = $this->decode($res);

        $this->assertEquals(422, $res->getStatusCode());
        $this->assertArrayHasKey('price', $body['errors']);
    }


    public function test_show_returns_product(): void
    {
        $product = $this->createProduct();

        $res  = $this->http->get("/api/products/{$product['id']}");
        $body = $this->decode($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($body['success']);
        $this->assertEquals($product['id'], $body['data']['id']);
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $res  = $this->http->get('/api/products/999999');
        $body = $this->decode($res);

        $this->assertEquals(404, $res->getStatusCode());
        $this->assertFalse($body['success']);
    }


    public function test_update_modifies_product(): void
    {
        $product = $this->createProduct();

        $res  = $this->http->put("/api/products/{$product['id']}", ['json' => [
            'price' => 199.99, 'quantity' => 99,
        ]]);
        $body = $this->decode($res);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($body['success']);
        $this->assertEquals(199.99, $body['data']['price']);
        $this->assertEquals(99, $body['data']['quantity']);
    }

    public function test_update_returns_404_for_missing_product(): void
    {
        $res = $this->http->put('/api/products/999999', ['json' => ['price' => 1.00]]);
        $this->assertEquals(404, $res->getStatusCode());
    }


    public function test_destroy_deletes_product(): void
    {
        $product = $this->createProduct();

        $res = $this->http->delete("/api/products/{$product['id']}");
        $this->assertEquals(200, $res->getStatusCode());

        $check = $this->http->get("/api/products/{$product['id']}");
        $this->assertEquals(404, $check->getStatusCode());
    }

    public function test_destroy_returns_404_for_missing_product(): void
    {
        $res = $this->http->delete('/api/products/999999');
        $this->assertEquals(404, $res->getStatusCode());
    }
}
