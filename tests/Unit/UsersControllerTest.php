<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexEndpointReturnsCorrectData()
    {
        // You can add more specific tests based on your requirements
        $response = $this->get('/api/v1/users');
        $response->assertStatus(200); // Assuming a successful response code

        // Add more assertions based on your expected data format or values
        // For example, you might want to assert that the response has a certain structure:
        $response->assertJsonStructure([
            '*' => [ // This assumes you have an array of users in your response
                'parentAmount',
                'Currency',
                'parentEmail',
                'statusCode',
                'registerationDate',
                'parentIdentification',
                'balance',
                'currency',
                'email',
                'status',
                'created_at',
                'id',
            ],
        ]);
    }

    // Test filtering by provider
    public function testIndexEndpointFiltersByProvider()
    {
        // Test the /api/v1/users?provider=DataProviderX scenario
        $response = $this->get('/api/v1/users?provider=DataProviderX');
        $response->assertStatus(200); // Assuming a successful response code

        // Add assertions based on the expected data for this filter
        // For example, assert that the response only contains data from DataProviderX
        $response->assertJsonFragment(['DataProviderX']);
        $response->assertJsonMissing(['DataProviderY']);
    }

    // Test filtering by statusCode
    public function testIndexEndpointFiltersByStatusCode()
    {
        // Test the /api/v1/users?statusCode=authorised scenario
        $response = $this->get('/api/v1/users?statusCode=authorised');
        $response->assertStatus(200); // Assuming a successful response code

        // Add assertions based on the expected data for this filter
        // For example, assert that the response only contains users with 'authorised' status
        $response->assertJsonFragment(['statusCode' => 1]);
        $response->assertJsonFragment(['status' => 100]);
    }

    // Test filtering by balance range
    public function testIndexEndpointFiltersByBalanceRange()
    {
        // Test the /api/v1/users?balanceMin=10&balanceMax=100 scenario
        $response = $this->get('/api/v1/users?balanceMin=10&balanceMax=100');
        $response->assertStatus(200); // Assuming a successful response code

        // Add assertions based on the expected data for this filter
        // For example, assert that the response only contains users with balance between 10 and 100
        $response->assertJsonFragment(['parentAmount' => 20]); // Assuming 'parentAmount' is used for balance in DataProviderX
        $response->assertJsonFragment(['balance' => 50]); // Assuming 'balance' is used for balance in DataProviderY
    }

    // Test filtering by currency
    public function testIndexEndpointFiltersByCurrency()
    {
        // Test the /api/v1/users?currency=USD scenario
        $response = $this->get('/api/v1/users?currency=USD');
        $response->assertStatus(200); // Assuming a successful response code

        // Add assertions based on the expected data for this filter
        // For example, assert that the response only contains users with 'USD' currency
        $response->assertJsonFragment(['Currency' => 'USD']);
        $response->assertJsonFragment(['currency' => 'USD']);
    }

    // Test combining all filters together
    public function testIndexEndpointCombinesAllFilters()
    {
        // Test the scenario with multiple filters combined
        $response = $this->get('/api/v1/users?provider=DataProviderX&statusCode=authorised&balanceMin=10&balanceMax=100&currency=USD');
        $response->assertStatus(200); // Assuming a successful response code

        // Add assertions based on the expected data for this combination of filters
        // For example, assert that the response only contains users matching all filter criteria
        $response->assertJsonFragment(['provider' => 'DataProviderX']);
        $response->assertJsonFragment(['statusCode' => 1]);
        $response->assertJsonFragment(['parentAmount' => 50]);
        $response->assertJsonFragment(['Currency' => 'USD']);
    }
}
