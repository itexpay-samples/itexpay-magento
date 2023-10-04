<?php

/*
 * Copyright (c) 2023 ItexPay
 *
 * Author: Marc Donald AHOURE
 * Email: dmcorporation2014@gmail.com
 *
 * Released under the GNU General Public License
 */

namespace Itex\ItexPay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\Store as Store;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{

    protected $method;

    public function __construct(PaymentHelper $paymentHelper, Store $store)
    {
        $this->method = $paymentHelper->getMethodInstance(\Itex\ItexPay\Model\Payment\ItexPay::CODE);
        $this->store = $store;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        
        $publicKey = $this->method->getConfigData('public_key');
       
        $integrationType = $this->method->getConfigData('integration_type')?: 'inline';

        return [
            'payment' => [
                \Itex\ItexPay\Model\Payment\ItexPay::CODE => [
                    'public_key' => $publicKey,
                    'integration_type' => $integrationType,
                    'api_url' => $this->store->getBaseUrl() . 'rest/',
                    'integration_type_standard_url' => $this->store->getBaseUrl() . 'itexpay/payment/setup',
                    'recreate_quote_url' => $this->store->getBaseUrl() . 'itexpay/payment/recreate',
                ]
            ]
        ];
    }
    
    public function getStore() {
        return $this->store;
    }
    
 
    public function getPublicKey(){
        $publicKey = $this->method->getConfigData('public_key');
       
        return $publicKey;
    }
    
    
}
