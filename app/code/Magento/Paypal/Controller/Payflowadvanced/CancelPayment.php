<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflowadvanced;

class CancelPayment extends \Magento\Paypal\Controller\Payflow\CancelPayment
{
    /**
     * Redirect block name
     * @var string
     */
    protected $_redirectBlockName = 'payflow.advanced.iframe';
}
