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
 * @since 2.0.0
 */
class ReturnUrl extends \Magento\Paypal\Controller\Payflow\ReturnUrl
{
    /**
     * Redirect block name
     * @var string
     * @since 2.0.0
     */
    protected $_redirectBlockName = 'payflow.advanced.iframe';

    /**
     * Payment method code
     * @var string
     * @since 2.1.0
     */
    protected $allowedPaymentMethodCodes = [
        Config::METHOD_PAYFLOWADVANCED
    ];
}
