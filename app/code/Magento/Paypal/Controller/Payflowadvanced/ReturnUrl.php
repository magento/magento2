<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller\Payflowadvanced;

use Magento\Paypal\Model\Config;

class ReturnUrl extends \Magento\Paypal\Controller\Payflow\ReturnUrl
{
    /**
     * Redirect block name
     * @var string
     */
    protected $_redirectBlockName = 'payflow.advanced.iframe';

    /**
     * Payment method code
     * @var string
     */
    protected $allowedPaymentMethodCodes = [
        Config::METHOD_PAYFLOWADVANCED
    ];
}
