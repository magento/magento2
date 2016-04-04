<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
