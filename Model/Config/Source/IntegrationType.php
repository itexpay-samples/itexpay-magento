<?php
/*
 * Copyright (c) 2023 ItexPay
 *
 * Author: Marc Donald AHOURE
 * Email: dmcorporation2014@gmail.com
 *
 * Released under the GNU General Public License
 */

namespace Itex\ItexPay\Model\Config\Source;

/**
 * Option source for Integration types
 * 
 * inline    : Popup type
 * standard  : Redirecting type
 * 
 */
class IntegrationType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'inline', 'label' => __('Inline - (Popup)')], ['value' => 'standard', 'label' => __('Standard - (Redirect)')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ["inline" => __('Inline - (Popup)'), 'standard' => __('Standard - (Redirect)')];
    }
}
