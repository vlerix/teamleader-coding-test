<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use App\Exceptions\DiscountException;

class CustomerRepository
{
	private $customers;
	
	function __construct()
	{
		$this->customers = $this->loadJSON('customers');
	}
	public function exists($id)
	{
		foreach ($this->customers as $customer) {
			
			if ($customer->id == $id) {
				return true;
			}
		}

		return false;
	}
	public function Find($id)
	{
		foreach ($this->customers as $customer) {
			
			if ($customer->id == $id) {
				return $customer;
			}
		}

		throw new DiscountException("Customer not found",404);
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