<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class DiscountController extends Controller
{
	private $customers;
	private $products;

    private $order;

	public function __construct()
	{
		// load example data

    	$this->customers = $this->loadJSON('customers');
    	$this->products = $this->loadJSON('products');
	}

    public function calculate(Request $request)
    {

    	// retrieve order from request data

    	$this->order = $request->json()->all();

    	// check if customer exists

    	if (!$this->getArrayField($this->order['customer-id'], 'id', $this->customers)) {
    		
    		$json_response = array(
    			'error' => true,
    			'error_message' => 'customer not found: '.$this->order['customer-id'],
    			'status_code' => 404);

    		return (new Response($json_response, 404))->header('Content-Type', 'application/json');
    	}

    	// check if products exist

    	foreach ($this->order['items'] as $item) {
    		
    		if (!$this->getArrayField($item['product-id'], 'id', $this->products)){

	    		$json_response = array(
	    			'error' => true,
	    			'error_message' => 'product not found: '.$item['product-id'],
	    			'status_code' => 404);

	    		return (new Response($json_response, 404))->header('Content-Type', 'application/json');    			
    		}

    	}

    	// prepare the response

        $responseData = array(
            'error' => false,
            'status_code' => 200,
        );

        $this->order['discounts'] = 0;

        // check discounts

        $responseData['order'] = $this->checkDiscount1()->checkDiscount2()->checkDiscount3()->getOrder();

        // return response

    	return (new Response($responseData, 200))->header('Content-Type', 'application/json');
    }
    /**
     * Discount1: a customer who has already bought for over € 1000, gets a discount of 10% on the whole order.
     * @return DiscountController
     */
    public function checkDiscount1()
    {
        $customer = $this->getArrayField($this->order['customer-id'], 'id', $this->customers)[0];

        if ($customer->revenue>1000) {

            $amount = number_format(round($this->order['total']*0.10,2), 2, '.', '');
            
            $this->order['total'] -= $amount;

            $this->order['discounts']++;

            $this->order['discountDetails']['discount'.$this->order['discounts']] = '10% discount on total order amount because customer revenue is over €1000';

        }

        return $this;
    }
    /**
     * Discount2: When an order item is of category Switches and quantity is 5, add one for free
     * @return DiscountController
     */
    public function checkDiscount2()
    {

        $orderItems = $this->order['items'];

        foreach ($orderItems as $index=>$item) {

            $product = $this->getArrayField($item['product-id'],'id',$this->products)[0];

            if ($product->category==2 && $item['quantity']==5) { // 5 items of category 2 = Switches

                $this->order['items'][$index]['quantity']++;

                $this->order['discounts']++;

                $this->order['discountDetails']['discount'.$this->order['discounts']] = '1 free Switch item per ordered quantity of 5 items';                
            }
        }

        return $this;
    } 
    /**
     * Discount3: If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product.
     * @return DiscountController
     */
    public function checkDiscount3()
    {

        $orderItems = $this->order['items'];

        $tools = $this->getArrayField(1,'category',$this->products);

        // sort by price: low -> high

        usort($tools, function($a, $b) {
            return $a->price - $b->price;
        });

        $cheapestTool = $tools[0];

        $toolsOrdered = 0;

        foreach ($orderItems as $index=>$item) {

            $product = $this->getArrayField($item['product-id'],'id',$this->products)[0];

            if ($product->category==1) { // category 1 = Tools

                $toolsOrdered += $item['quantity'];

                if($product->id == $cheapestTool->id)
                    $cheapestToolOrderItem = $index; // remember the order item with the cheapest tool
               
            }
        }

        if ($toolsOrdered >= 2 ) {

            $discountAmount = number_format(round($cheapestTool->price*0.10,2), 2, '.', '');
            $totalDiscountAmount = $discountAmount*$this->order['items'][$cheapestToolOrderItem]['quantity'];

            $this->order['items'][$cheapestToolOrderItem]['unit-price'] -= $discountAmount;
            $this->order['items'][$cheapestToolOrderItem]['total'] -= $totalDiscountAmount;
            $this->order['total'] -= $totalDiscountAmount;

            $this->order['discounts']++;
            $this->order['discountDetails']['discount'.$this->order['discounts']] = '20% discount on cheapest Tool product because customer ordered 2 or more Tool products';

        }

        return $this;
    }     
    /**
        * Load local test data
        * @param string $filename 
        * @return array
        */   
    private function loadJSON($filename)
    {
	    $path = storage_path() ."/app/${filename}.json"; 

	    $file = File::get($path); 

	    $data = json_decode($file);
	    
	    return $data;
    }
    private function getArrayField($needle, $needle_field, $haystack) { 

        $result = array();

        foreach ($haystack as $item) 
            if (isset($item->$needle_field) && $item->$needle_field == $needle) 
                $result[] = $item; 

	    return $result; 
	} 
    public function getOrder()
    {
        return $this->order;
    }

}
