<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\UrlInterface;
use Magento\TestFramework\ObjectManager;

$url = ObjectManager::getInstance()->get(UrlInterface::class);
$cancelUrl = $url->getUrl('paypal/express/cancel/');
$successUrl = $url->getUrl('checkout/onepage/success/');
$returnUrl = $url->getUrl('paypal/express/return/');
$pendingUrl = $url->getUrl('checkout/onepage/pending/');



return [
        'PAYMENTACTION' => 'Authorization',
        'AMT' => '30.00',
        'CURRENCYCODE' => 'USD',
        'RETURNURL' => $returnUrl,
        'CANCELURL' => $cancelUrl,
        'INVNUM' => 'test_quote',
        'SOLUTIONTYPE' => 'Mark',
        'GIROPAYCANCELURL' =>  $cancelUrl,
        'GIROPAYSUCCESSURL' => $successUrl,
        'BANKTXNPENDINGURL' =>  $pendingUrl,
        'SHIPPINGAMT' => '10.00',
        'ITEMAMT' => '20.00',
        'TAXAMT' => '0.00',
        'L_NUMBER0' => null,
        'L_NAME0' => 'Simple Product',
        'L_QTY0' => 2,
        'L_AMT0' => '10.00',
        'BUSINESS' => 'CompanyName',
        'NOTETEXT' => null,
        'EMAIL' => 'customer@example.com',
        'FIRSTNAME' => 'John',
        'LASTNAME' => 'Smith',
        'MIDDLENAME' => null,
        'SALUTATION' => null,
        'SUFFIX' => null,
        'COUNTRYCODE' => 'US',
        'STATE' => 'AL',
        'CITY' => 'CityM',
        'STREET' => 'Green str, 67',
        'ZIP' => '75477',
        'PHONENUM' => '3468676',
        'SHIPTOCOUNTRYCODE' => 'US',
        'SHIPTOSTATE' => 'AL',
        'SHIPTOCITY' => 'CityM',
        'SHIPTOSTREET' => 'Green str, 67',
        'SHIPTOZIP' => '75477',
        'SHIPTOPHONENUM' => '3468676',
        'SHIPTOSTREET2' => '',
        'STREET2' => '',
        'SHIPTONAME' => 'John Smith',
        'ADDROVERRIDE' => 1
];
