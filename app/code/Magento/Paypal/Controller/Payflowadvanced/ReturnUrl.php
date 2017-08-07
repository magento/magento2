<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflowadvanced;

use Magento\Paypal\Model\Config;

/**
 * Class \Magento\Paypal\Controller\Payflowadvanced\ReturnUrl
 *
 */
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
     * @since 2.0.1
     */
    protected $allowedPaymentMethodCodes = [
        Config::METHOD_PAYFLOWADVANCED
    ];
}
