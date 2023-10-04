<?php

/*
 * Copyright (c) 2023 ItexPay
 *
 * Author: Marc Donald AHOURE
 * Email: dmcorporation2014@gmail.com
 *
 * Released under the GNU General Public License
 */

namespace Itex\ItexPay\Controller\Payment;

class Setup extends AbstractItexPayStandard {

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        
   
        $message = '';
        $order = $this->orderInterface->loadByIncrementId($this->checkoutSession->getLastRealOrder()->getIncrementId());

        if ($order && $this->method->getCode() == $order->getPayment()->getMethod()) {

            try {

               return  $this->processAuthorization($order);
            } 
            
            catch (Exception $e) {

                $message = $e->getMessage();
                $order->addStatusToHistory($order->getStatus(), $message);
                $this->orderRepository->save($order);

                $this->messageManager->addErrorMessage("An unexpected error occurred by ".$message);
                return $this->_redirect('checkout/onepage/failure');

            }
        }

        else
        {
             $this->messageManager->addErrorMessage("An unexpected error occurred during the order processing");
            return $this->_redirect('checkout/onepage/failure');
        }
        
    }




 protected function processAuthorization(\Magento\Sales\Model\Order $order) {
 

//getting environment...
$environment = $this->method->getConfigData('test_mode');

    if($environment == 0)
    { 
       $api_base_url = "https://api.itexpay.com/api/pay";
    }

    else
    { 
      $api_base_url = "https://staging.itexpay.com/api/pay";
    }



    
 //ApI KEy 
 $apikey = $this->method->getConfigData('public_key');
 
/*
      //Generating 12 unique random transaction id...
$transaction_id='';
$allowed_characters = array(1,2,3,4,5,6,7,8,9,0); 
for($i = 1;$i <= 12; $i++){ 
    $transaction_id .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
 
} 
*/

$transaction_id = $this->checkoutSession->getLastRealOrder()->getIncrementId();


$firstname = $order->getCustomerFirstname(); 
 //Remove space between firstname...
$firstname = preg_replace('/\s+/', '', $firstname);

 //$phonenumber = $order->getCustomAttribute('telephone')->getValue();

    //Customer International number...
    $phonenumber = "23470022554839"; 

$callback_url =  $this->configProvider->store->getBaseUrl() . "itexpay/payment/callback";

//Get store currecny..
 $currency = $this->configProvider->store->getDefaultCurrencyCode();



        //itexpay Checkout Api Payload...
    $data = array(
    "amount"  => $order->getGrandTotal(),
    "currency" => $currency, 
     "redirecturl" => $callback_url,
     "customer" =>  array('email' => $order->getCustomerEmail(), 
                        'first_name' =>  $firstname, 
                        'last_name' => $order->getCustomerLastname(), 
                        'phone_number' => $phonenumber ),
          "reference" => $transaction_id,
     
);



//Encoding playload...
$json_data = json_encode($data);

//Api base URL...
 $url = $api_base_url;                                                                                                            
// Initialization of the request
$curl = curl_init();

// Definition of request's headers
curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_ENCODING => "json",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer ".$apikey."",
    "cache-control: no-cache",
    "content-type: application/json; charset=UTF-8",
    
  ),
   CURLOPT_POSTFIELDS => $json_data,
));

// Send request and show response
$response = curl_exec($curl);
$err = curl_error($curl);


curl_close($curl);

if ($err) {
  //echo "API Error #:" . $err;
    //Api error if any...
    // return  $err;
     $this->messageManager->addErrorMessage("An unforeseen network error has occurred at the payment gateway level");
    $this->_redirect('checkout/onepage/failure');


} else {

  
    $response_data = json_decode($response, true);
        


if (!isset($response_data['amount'])) {
    $amount = null;
}

else
{
    $amount = $response_data['amount'];
}

if (!isset($response_data['currency'])) {
   $currency  = null;
}

else
{
    $currency = $response_data['currency'];
}


if (!isset($response_data['paid'])) {
    $paid = null;
}

else
{
    $paid = $response_data['paid'];
}

if (!isset($response_data['status'])) {
     $status  = null;
}

else
{
    $status = $response_data['status'];
}



if (!isset($response_data['env'])) {
    $env = null;
}



else
{
    $env = $response_data['env'];
}

if (!isset($response_data['reference'])) {
   $reference  = null;
}

else
{
    $reference = $response_data['reference'];
}


if (!isset($response_data['paymentid'])) {
     $paymentid = null;
}

else
{
    $paymentid = $response_data['paymentid'];
}

if (!isset($response_data['authorization_url'])) {
    $authorization_url  = null;
}

else
{
    $authorization_url = $response_data['authorization_url'];
}

if (!isset($response_data['failure_message'])) {
    $failure_message  = null;
}

else
{
    $failure_message = $response_data['failure_message'];
}



if($status == "successful" && $paid == false)
{ 


     
    //Redirect to checkout page...
    $redirectFactory = $this->resultRedirectFactory->create();
        $redirectFactory->setUrl($authorization_url);

        return $redirectFactory;
        
      

}

   
    else
    {   
    
      
       $this->messageManager->addErrorMessage("An unforeseen error has occurred by ".$failure_message);
       return  $this->_redirect('checkout/onepage/failure');
    
      
    }


}// end of main else..
    


    } // end of processAuthorization...



}
