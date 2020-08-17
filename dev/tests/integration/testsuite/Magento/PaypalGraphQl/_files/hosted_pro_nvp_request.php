<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

//phpcs:ignorefile
use Magento\Framework\UrlInterface;
use Magento\TestFramework\ObjectManager;

$url = ObjectManager::getInstance()->create(UrlInterface::class);
$cancelUrl = $url->getUrl('paypal/hostedpro/customcancel');
$returnUrl = $url->getUrl('paypal/hostedpro/customreturn');

return [
    'METHOD' => 'BMCreateButton',
    'BUTTONCODE' => 'TOKEN',
    'BUTTONTYPE' => 'PAYMENT',
    'L_BUTTONVAR0' => 'invoice=test_quote',
    'L_BUTTONVAR1' => 'address_override=true',
    'L_BUTTONVAR2' => 'currency_code=USD',
    'L_BUTTONVAR3' => 'buyer_email=guest@example.com',
    'L_BUTTONVAR4' => 'billing_first_name=John',
    'L_BUTTONVAR5' => 'billing_last_name=Smith',
    'L_BUTTONVAR6' => 'billing_city=CityM',
    'L_BUTTONVAR7' => 'billing_state=AL',
    'L_BUTTONVAR8' => 'billing_zip=75477',
    'L_BUTTONVAR9' => 'billing_country=US',
    'L_BUTTONVAR10' => 'billing_address1=Green str, 67',
    'L_BUTTONVAR11' => 'billing_address2=',
    'L_BUTTONVAR12' => 'first_name=John',
    'L_BUTTONVAR13' => 'last_name=Smith',
    'L_BUTTONVAR14' => 'city=CityM',
    'L_BUTTONVAR15' => 'state=AL',
    'L_BUTTONVAR16' => 'zip=75477',
    'L_BUTTONVAR17' => 'country=US',
    'L_BUTTONVAR18' => 'address1=Green str, 67',
    'L_BUTTONVAR19' => 'address2=',
    'L_BUTTONVAR20' => 'paymentaction=authorization',
    'L_BUTTONVAR21' => 'notify_url=http://localhost/index.php/paypal/ipn/',
    'L_BUTTONVAR22' => 'cancel_return=' . $cancelUrl,
    'L_BUTTONVAR23' => 'return=' . $returnUrl,
    'L_BUTTONVAR24' => 'lc=US',
    'L_BUTTONVAR25' => 'template=mobile-iframe',
    'L_BUTTONVAR26' => 'showBillingAddress=false',
    'L_BUTTONVAR27' => 'showShippingAddress=true',
    'L_BUTTONVAR28' => 'showBillingEmail=false',
    'L_BUTTONVAR29' => 'showBillingPhone=false',
    'L_BUTTONVAR30' => 'showCustomerName=false',
    'L_BUTTONVAR31' => 'showCardInfo=true',
    'L_BUTTONVAR32' => 'showHostedThankyouPage=false',
    'L_BUTTONVAR33' => 'subtotal=20.00',
    'L_BUTTONVAR34' => 'total=30.00',
    'L_BUTTONVAR35' => 'tax=0.00',
    'L_BUTTONVAR36' => 'shipping=10.00',
    'L_BUTTONVAR37' => 'discount=0.00',
];
