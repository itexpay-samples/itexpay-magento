/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'itex_itexpay',
                component: 'Itex_ItexPay/js/view/payment/method-renderer/itex_itexpay-method'
            }
        );

        /** Add view logic here if needed */
        
        return Component.extend({});
    }
);