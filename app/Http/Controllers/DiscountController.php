<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\DiscountService;

class DiscountController extends Controller
{
    public function calculate(Request $request)
    {

    	// retrieve order from request data

    	$order = $request->json()->all();

        $discountService = new DiscountService();

        // run discounts

        $processedOrder = $discountService->runDiscounts($order);

    	// prepare the response

        $responseData = array(
            'error' => false,
            'status_code' => 200,
        );


        $responseData['order'] = $processedOrder;

        // return response

    	return (new Response($responseData, 200))->header('Content-Type', 'application/json');
    }

}
