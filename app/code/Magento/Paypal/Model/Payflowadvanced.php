<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Payments Advanced gateway model
 */
namespace Magento\Paypal\Model;

class Payflowadvanced extends \Magento\Paypal\Model\Payflowlink
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED;

    /**
     * Type of block that generates method form
     *
     * @var string
     */
    protected $_formBlockType = \Magento\Paypal\Block\Payflow\Advanced\Form::class;

    /**
     * Type of block that displays method information
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Paypal\Block\Payflow\Advanced\Info::class;

    /**
     * Controller for callback urls
     *
     * @var string
     */
    protected $_callbackController = 'payflowadvanced';
}
