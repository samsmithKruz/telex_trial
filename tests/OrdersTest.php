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
    public function testBasicAssertions()
    {
        $this->assertTrue(true);
        $this->assertEquals(5, 2 + 3);
    }
}
