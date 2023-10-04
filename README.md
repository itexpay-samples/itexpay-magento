
## ItexPay Magento 2 Module

ItexPay Magento 2 payment module

## Installation

* Go to your Magento 2 root folder

* Upload the ItexPay folder into your Magento directory e.g. {Magento_root_folder}/app/code

N.B.: If code folder does not exist please create it. also if you have downloaded the zip file , kindly unzip file and upload the content. 


* Enter following commands to enable module:

```bash
php bin/magento module:enable Itex_ItexPay 
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento indexer:reindex
php bin/magento cache:clean
```

## Configuration

To configure the plugin in *Magento Admin* , go to __Stores > Configuration > Sales > Payment Methods__  from the left hand menu,  from the list of options. You will see __ItexPay__ as part of the available Payment Methods. Click on it to configure the payment gateway.

* __Enabled__ - Select _Yes_ to enable ItexPay Payment Gateway.
* __Title__ - allows you to determine what your customers will see this payment option as on the checkout page.
* __Test Mode__ - Select Yes to enable test mode or No to enable live mode. Test mode enables you to test payments before going live. If you ready to start receving real payment on your site, kindly select Yes.
* __Public Key__ - Enter your Public Key here. Get your API keys from your account (https://dashboard.itexpay.com/signin)

* Click on __Save Config__ for the changes you made to be effected.



## Documentation

* [ItexPay Documentation](https://itexpay.com/docs)
