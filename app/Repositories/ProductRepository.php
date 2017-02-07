<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use App\Exceptions\DiscountException;

class ProductRepository
{
	private $products;
	
	function __construct()
	{
		$this->products = $this->loadJSON('products');
	}
	public function exists($id)
	{
		foreach ($this->products as $product) {
			
			if ($product->id == $id) {
				return true;
			}
		}

		return false;
	}
	public function Find($id)
	{
		foreach ($this->products as $product) {
			
			if ($product->id == $id) {
				return $product;
			}
		}

		throw new DiscountException("Product not found",404);
	}
	public function FindBy($field, $value)
	{
		$results = array();

		foreach ($this->products as $product) {
			
			if ($product->$field == $value) {
				$results[]= $product;
			}
		}		

		if (!$results) {
			throw new DiscountException("No products found with criteria {$field}={$value}",404);
		}

		return $results;
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
}