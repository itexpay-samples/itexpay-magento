<?php
/*
 * Copyright (c) 2023 ItexPay
 *
 * Author: Marc Donald AHOURE
 * Email: dmcorporation2014@gmail.com
 *
 * Released under the GNU General Public License
 */

namespace Itex\ItexPay\Model;

use Exception;
use Magento\Payment\Helper\Data as PaymentHelper;
use Itex\ItexPay\Model\Payment\ItexPay as ItexPayModel;
use Yabacon\ItexPay as ItexPayLib;

class PaymentManagement implements \Itex\ItexPay\Api\PaymentManagementInterface
{

    protected $ItexPayPaymentInstance;

    protected $ItexPayLib;
    
    protected $orderInterface;
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Checkout\Model\Session $checkoutSession
            
    ) {
        $this->eventManager = $eventManager;
        $this->ItexPayPaymentInstance = $paymentHelper->getMethodInstance(ItexPayModel::CODE);
        
        $this->orderInterface = $orderInterface;
        $this->checkoutSession = $checkoutSession;

        $public_Key = $this->ItexPayPaymentInstance->getConfigData('public_key');
        
        $this->ItexPayLib = new ItexPayLib($public_Key);
    }

    /**
     * @param string $reference
     * @return bool
     */
    public function verifyPayment($reference)
    {
        
        // we are appending quoteid
        $ref = explode('_-~-_', $reference);
        $reference = $ref[0];
        $quoteId = $ref[1];
        
        try {
            $transaction_details = $this->ItexPayLib->transaction->verify([
                'reference' => $reference
            ]);
            
            $order = $this->getOrder();
            //return json_encode($transaction_details);
            if ($order && $order->getQuoteId() === $quoteId && $transaction_details->data->metadata->quoteId === $quoteId) {
                
                // dispatch the `ItexPay_payment_verify_after` event to update the order status
                $this->eventManager->dispatch('ItexPay_payment_verify_after', [
                    "ItexPay_order" => $order,
                ]);

                return json_encode($transaction_details);
            }
        } catch (Exception $e) {
            return json_encode([
                'status'=>0,
                'message'=>$e->getMessage()
            ]);
        }
        return json_encode([
            'status'=>0,
            'message'=>"quoteId doesn't match transaction"
        ]);
    }

    /**
     * Loads the order based on the last real order
     * @return boolean
     */
    private function getOrder()
    {
        // get the last real order id
        $lastOrder = $this->checkoutSession->getLastRealOrder();
        if($lastOrder){
            $lastOrderId = $lastOrder->getIncrementId();
        } else {
            return false;
        }
        
        if ($lastOrderId) {
            // load and return the order instance
            return $this->orderInterface->loadByIncrementId($lastOrderId);
        }
        return false;
    }

}
