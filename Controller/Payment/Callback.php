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

class Callback extends AbstractItexPayStandard {

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {

        $reference = $this->request->get('reference');
        $message = "";
        
        if (!$reference) {
            return $this->redirectToFinal(false, "No reference supplied");
        }
        
        try {
            $transactionDetails = $this->ItexPay->transaction->verify([
                'reference' => $reference
            ]);
            
            $reference = explode('_', $transactionDetails->data->reference, 2);
            $reference = ($reference[0])?: 0;
            
            $order = $this->orderInterface->loadByIncrementId($reference);
            
            if ($order && $reference === $order->getIncrementId()) {
                // dispatch the `payment_verify_after` event to update the order status
                
                $this->eventManager->dispatch('ItexPay_payment_verify_after', [
                    "ItexPay_order" => $order,
                ]);

                return $this->redirectToFinal(true);
            }

            $message = "Invalid reference or order number";
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            
        } 

        return $this->redirectToFinal(false, $message);
    }

}
