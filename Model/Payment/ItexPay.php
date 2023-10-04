<?php
/*
 * Copyright (c) 2023 ItexPay
 *
 * Author: Marc Donald AHOURE
 * Email: dmcorporation2014@gmail.com
 *
 * Released under the GNU General Public License
 */

namespace Itex\ItexPay\Model\Payment;

/**
 * ItexPay main payment method model
 * 
 */
class ItexPay extends \Magento\Payment\Model\Method\AbstractMethod
{

    const CODE = 'itex_itexpay';
    
    protected $_code = self::CODE;
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
