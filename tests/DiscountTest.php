<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DiscountTest extends TestCase
{

    public function testCustomerNotFound()
    {
        $this->postJson('/api/discount', json_decode('{
		  "id": "1",
		  "customer-id": "5",
		  "items": [
		    {
		      "product-id": "B102",
		      "quantity": "10",
		      "unit-price": "4.99",
		      "total": "49.90"
		    }
		  ],
		  "total": "49.90"
}',true))
             ->seeJson([
                 'error' => true,
                 'status_code' => 404
             ]);
    }
    public function testProductNotFound()
    {
        $this->postJson('/api/discount', json_decode('{
		  "id": "1",
		  "customer-id": "2",
		  "items": [
		    {
		      "product-id": "B108",
		      "quantity": "10",
		      "unit-price": "4.99",
		      "total": "49.90"
		    }
		  ],
		  "total": "49.90"
}',true))
             ->seeJson([
                 'error' => true,
                 'status_code' => 404
             ]);
    }  
    public function testOverallDiscount()
    {
        $this->postJson('/api/discount', json_decode('{
  "id": "2",
  "customer-id": "2",
  "items": [
    {
      "product-id": "B102",
      "quantity": "4",
      "unit-price": "4.99",
      "total": "24.95"
    }
  ],
  "total": "24.95"
}',true))
             ->seeJson([
                 'error' => false,
                 'status_code' => 200,
                 'discounts' => 1,
                 'total' => 22.45
             ]);
    } 
    public function testSwitchesDiscount()
    {
        $this->postJson('/api/discount', json_decode('{
  "id": "1",
  "customer-id": "1",
  "items": [
    {
      "product-id": "B102",
      "quantity": "5",
      "unit-price": "4.99",
      "total": "49.90"
    }
  ],
  "total": "49.90"
}',true))
             ->seeJson([
                 'error' => false,
                 'status_code' => 200,
                 'discounts' => 1,
                 'quantity' => 6
             ]);
    }    
    public function testToolsDiscount()
    {
        $this->postJson('/api/discount', json_decode('{
  "id": "3",
  "customer-id": "3",
  "items": [
    {
      "product-id": "A101",
      "quantity": "2",
      "unit-price": "9.75",
      "total": "19.50"
    }
  ],
  "total": "19.50"
}',true))
             ->seeJson([
                 'error' => false,
                 'status_code' => 200,
                 'discounts' => 1,
                 'unit-price' => 7.80
             ]);
    }     
    public function test1Discount()
    {
        $this->postJson('/api/discount', json_decode('{
  "id": "3",
  "customer-id": "3",
  "items": [
    {
      "product-id": "A101",
      "quantity": "2",
      "unit-price": "9.75",
      "total": "19.50"
    },
    {
      "product-id": "A102",
      "quantity": "1",
      "unit-price": "49.50",
      "total": "49.50"
    }
  ],
  "total": "69.00"
}',true))
             ->seeJson([
                 'error' => false,
                 'status_code' => 200,
                 'discounts' => 1,
                 'total' => 65.10
             ]);
    } 
    public function test2Discounts()
    {
        $this->postJson('/api/discount', json_decode('{
  "id": "2",
  "customer-id": "2",
  "items": [
    {
      "product-id": "B102",
      "quantity": "5",
      "unit-price": "4.99",
      "total": "24.95"
    }
  ],
  "total": "24.95"
}',true))
             ->seeJson([
                 'error' => false,
                 'status_code' => 200,
                 'discounts' => 2,
                 'total' => 22.45
             ]);
    } 
    public function testNoDiscounts()
    {
        $this->postJson('/api/discount', json_decode('{
  "id": "1",
  "customer-id": "1",
  "items": [
    {
      "product-id": "B102",
      "quantity": "10",
      "unit-price": "4.99",
      "total": "49.90"
    }
  ],
  "total": "49.90"
}',true))
             ->seeJson([
                 'error' => false,
                 'status_code' => 200,
                 'discounts' => 0,
             ]);
    }     
}
