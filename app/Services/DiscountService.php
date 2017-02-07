<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Repositories\CustomerRepository;
use App\Exceptions\DiscountException;

class DiscountService
{
	private $order;

	private $productRepo;
	private $customerRepo;
	
	public function runDiscounts(array $order)
	{
		$this->order = $order;

		$this->productRepo = new ProductRepository();
		$this->customerRepo = new CustomerRepository();

		// check if customer exists

		if(!$this->customerRepo->exists($order['customer-id'])) {

			throw new DiscountException("customer not found: ".$order['customer-id'],404);
		}

		// check if products exist

    	foreach ($this->order['items'] as $item) {
    		
    		if (!$this->productRepo->exists($item['product-id'])){

				throw new DiscountException("product not found: ".$item['product-id'],404); 			
    		}
    	}

    	$this->order['discounts'] = 0;

    	// A customer who has already bought for over € 1000, gets a discount of 10% on the whole order.
    	$this->runDiscount1(1000,10);

    	// For every products of category "Switches" (id 2), when you buy five, you get a sixth for free.
    	$this->runDiscount2(2,5,1);

    	// If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product.
    	$this->runDiscount3(1,2,20);

    	return $this->order;

	}

	/**
	 * Discount1: a customer who has already bought for over a specific, gets a percentual discount on the whole order.
	 * @param int $amountThreshold 
	 * @param int $discountPercentage 
	 * @return void
	 */
	private function runDiscount1($amountThreshold, $discountPercentage)
	{
        $customer = $this->customerRepo->find($this->order['customer-id']);

        if ($customer->revenue>$amountThreshold) {

            $amount = number_format(round($this->order['total']*($discountPercentage/100),2), 2, '.', '');
            
            $this->order['total'] -= $amount;

            $this->order['discounts']++;

            $this->order['discountDetails']['discount'.$this->order['discounts']] = "{$discountPercentage}% discount on total order amount because customer revenue is over €{$amountThreshold}";

        }
	}

    /**
     * Discount2: When an order item is of a specific category and quantity is x, add y items for free
     * @param int $productCategory 
     * @param int $quantityThreshold 
     * @param int $itemsForFree 
     * @return void
     */
    private function runDiscount2($productCategory, $quantity, $itemsForFree)
    {

        $orderItems = $this->order['items'];

        foreach ($orderItems as $index=>$item) {

            $product = $this->productRepo->find($item['product-id']);

            if ($product->category==$productCategory && $item['quantity']==$quantity) { 

                $this->order['items'][$index]['quantity'] += $itemsForFree;

                $this->order['discounts']++;

                $this->order['discountDetails']['discount'.$this->order['discounts']] = "{$itemsForFree} free item(s) of product category {$productCategory} per ordered quantity of {$quantity} item(s)";                
            }
        }
    } 	

    /**
     * Discount3: If you buy two or more products of category x, you get a discount on the cheapest product.
     * @param int $productCategory 
     * @param int $quantityThreshold 
     * @param int $discountPercentage 
     * @return void
     */
    private function runDiscount3($productCategory, $quantityThreshold, $discountPercentage)
    {

        $orderItems = $this->order['items'];

        $tools = $this->productRepo->findBy('category',$productCategory);

        // sort by price: low -> high

        usort($tools, function($a, $b) {
            return $a->price - $b->price;
        });

        $cheapestTool = $tools[0];

        $toolsOrdered = 0;

        foreach ($orderItems as $index=>$item) {

            $product = $this->productRepo->find($item['product-id']);

            if ($product->category==$productCategory) { 

                $toolsOrdered += $item['quantity'];

                if($product->id == $cheapestTool->id)
                    $cheapestToolOrderItem = $index; // remember the order item with the cheapest tool
               
            }
        }

        if ($toolsOrdered >= $quantityThreshold ) {

            $discountAmount = number_format(round($cheapestTool->price*($discountPercentage/100),2), 2, '.', '');
            $totalDiscountAmount = $discountAmount*$this->order['items'][$cheapestToolOrderItem]['quantity'];

            $this->order['items'][$cheapestToolOrderItem]['unit-price'] -= $discountAmount;
            $this->order['items'][$cheapestToolOrderItem]['total'] -= $totalDiscountAmount;
            $this->order['total'] -= $totalDiscountAmount;

            $this->order['discounts']++;
            $this->order['discountDetails']['discount'.$this->order['discounts']] = "{$discountPercentage}% discount on cheapest product of category {$productCategory} because customer ordered {$quantityThreshold} or more category {$productCategory} products";

        }
    }         
}