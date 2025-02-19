<?php

use PHPUnit\Framework\TestCase;

class OrdersTest extends TestCase
{
    private $baseUrl;

    protected function setUp(): void
    {
        // Set a base URL for testing, modify as needed
        $this->baseUrl = getenv('APP_URL');
        sendRequest("{$this->baseUrl}/migrate");
    }
    public function testPlaceOrderSuccessfully()
    {
        $data = [
            'txn_id' => 'TXN12345',
            'product_id' => 10,
            'description' => 'Sample product order',
            'amount' => 5000,
            'user_id' => 1
        ];

        $response = sendRequest("{$this->baseUrl}/integration/placeorder", 'POST', $data);
        // Assert response is an array
        $this->assertIsArray($response);

        // Assert response contains success message
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Order placed successfully', $response['message']);
    }

    public function testPlaceOrderWithMissingFields()
    {
        $data = [
            'txn_id' => 'TXN12345',
            // Missing product_id, description, amount, user_id
        ];

        $response = sendRequest("{$this->baseUrl}/integration/placeorder", 'POST', $data);

        // Assert response is an array
        $this->assertIsArray($response);

        // Assert response contains error message
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('Payload to place order must contain', $response['message']);
    }

    public function testPlaceOrderWithInvalidMethod()
    {
        $response = sendRequest("{$this->baseUrl}/integration/placeorder", 'GET');

        // Assert response is an array
        $this->assertIsArray($response);

        // Assert response contains error message for invalid method
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('This route requires POST method', $response['message']);
    }
    public function testPlaceAndCancelOrder()
    {
        // Step 1: Place an order
        $orderData = [
            'txn_id' => '12345',
            'product_id' => 98765,
            'description' => 'Test order',
            'amount' => 100.50,
            'user_id' => 1
        ];

        $placeOrderResponse = sendRequest("{$this->baseUrl}/integration/placeorder", 'POST', $orderData);

        // Assert order was placed successfully
        $this->assertIsArray($placeOrderResponse);
        $this->assertArrayHasKey('message', $placeOrderResponse);
        $this->assertEquals('Order placed successfully', $placeOrderResponse['message']);

        // Step 2: Get the order list
        $orders = sendRequest("{$this->baseUrl}/integration/listorders", 'GET');

        // Assert order list is an array
        $this->assertIsArray($orders);
        $this->assertNotEmpty($orders);

        // Get the latest order ID
        $order_id = $orders[0]['order_id'] ?? null;
        $this->assertNotNull($order_id, "Order ID should not be null");

        // Step 3: Cancel the order
        $cancelResponse = sendRequest("{$this->baseUrl}/integration/cancelorder/{$order_id}", 'POST');

        // Assert order was cancelled successfully
        $this->assertIsArray($cancelResponse);
        $this->assertArrayHasKey('message', $cancelResponse);
        $this->assertEquals('Order cancelled successfully', $cancelResponse['message']);
    }
    public function testDailySummarizer(){
        $orderData = [
            'txn_id' => '12345',
            'product_id' => 98765,
            'description' => 'Test order',
            'amount' => 100.50,
            'user_id' => 1
        ];

        $placeOrderResponse = sendRequest("{$this->baseUrl}/integration/placeorder", 'POST', $orderData);
        sendRequest("{$this->baseUrl}/integration/backdateorder/1", 'POST', $orderData);

        $response = sendRequest("{$this->baseUrl}/integration/webhook", 'GET');

        $this->assertIsArray($response);

        // Assert response contains success message
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }
}
