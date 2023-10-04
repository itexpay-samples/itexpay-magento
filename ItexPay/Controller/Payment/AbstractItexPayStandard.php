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

use Magento\Payment\Helper\Data as PaymentHelper;

abstract class AbstractItexPayStandard extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory;
    
    /**
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface 
     */
    protected $orderRepository;
    
    /**
     *
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;
    protected $checkoutSession;
    protected $method;
    protected $messageManager;
    
    /**
     *
     * @var \Itex\ItexPay\Model\Ui\ConfigProvider 
     */
    protected $configProvider;
    
    /**
     *
     * @var \Yabacon\ItexPay 
     */
    protected $ItexPay;
    
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     *
     * @var \Magento\Framework\App\Request\Http 
     */
    protected $request;

  

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Sales\Api\Data\OrderInterface $orderInterface,
            \Magento\Checkout\Model\Session $checkoutSession,
            PaymentHelper $paymentHelper,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Itex\ItexPay\Model\Ui\ConfigProvider $configProvider,
            \Magento\Framework\Event\Manager $eventManager,
            \Magento\Framework\App\Request\Http $request,
            \Psr\Log\LoggerInterface $logger,

             

    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;
        $this->method = $paymentHelper->getMethodInstance(\Itex\ItexPay\Model\Payment\ItexPay::CODE);
        $this->messageManager = $messageManager;
        $this->configProvider = $configProvider;
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->logger = $logger;
        

        parent::__construct($context);
    }
    


    protected function redirectToFinal()
    {
       

//Getting order instace...
$order = $this->orderInterface->loadByIncrementId($this->checkoutSession->getLastRealOrder()->getIncrementId());

//Geeting order Reference....
$reference_id = $this->checkoutSession->getLastRealOrder()->getIncrementId();


//getting environment...
$environment = $this->method->getConfigData('test_mode');

        if($environment == 0)
    { 
        $status_check_base_url = 'https://api.itexpay.com/api/v1/transaction/status?merchantreference='.$reference_id;
    }

    else
    { 
      $status_check_base_url = 'https://staging.itexpay.com/api/v1/transaction/status?merchantreference='.$reference_id;
    }
 

 //ApI KEy 
 $api_key = $this->method->getConfigData('public_key');


// Initialize cURL session
$ch = curl_init();

 

// Set the cURL options
curl_setopt($ch, CURLOPT_URL, $status_check_base_url); // URL to send the request to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Connection timeout in seconds
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Overall timeout in seconds

// Set custom headers
$headers = array(
    'Authorization: Bearer '.$api_key.'', 
);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Set the custom headers

// Execute the cURL request and get the response
$response = curl_exec($ch);

$response_data = json_decode($response, true );


// Check for cURL errors
if (curl_errno($ch)) {
   // echo 'cURL error: ' . curl_error($ch);
    //die("<h2 style=color:red>" . curl_error($ch)." </h2>");
    $this->messageManager->addErrorMessage("An unforeseen network error has occurred at the payment gateway level");
      return $this->_redirect('checkout/onepage/failure');

} else {



 if (!isset($response_data['code'])) {
     $transaction_code = null;
 }

 else
 {
     $transaction_code = $response_data['code'];
 }

 if (!isset($response_data['message'])) {
     $transaction_message = null;
 }

 else
 {
     $transaction_message = $response_data['message'];
 }


  //checking if transaction is successful
    if($transaction_code == "00")
    { 

       
       $this->messageManager->addSuccessMessage("Payment received, order is processing ");
       $order->addStatusToHistory("processing", $transaction_message);
                $this->orderRepository->save($order);
        return $this->_redirect('checkout/onepage/success');
    

      
    }

   

else
{
    $this->messageManager->addErrorMessage(__($transaction_message));
    $order->addStatusToHistory("canceled", $transaction_message);
                $this->orderRepository->save($order);
      return $this->_redirect('checkout/onepage/failure');
}



}

// Close the cURL session
curl_close($ch);



    } // end of redirectToFinal...
    
    
}
