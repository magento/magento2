<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Payments Advanced gateway model
 */
namespace Magento\Paypal\Model;

/**
 * Class \Magento\Paypal\Model\Payflowadvanced
 *
 * @since 2.0.0
 */
class Payflowadvanced extends \Magento\Paypal\Model\Payflowlink
{
    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED;

    /**
     * Type of block that generates method form
     *
     * @var string
     * @since 2.0.0
     */
    protected $_formBlockType = \Magento\Paypal\Block\Payflow\Advanced\Form::class;

    /**
     * Type of block that displays method information
     *
     * @var string
     * @since 2.0.0
     */
    protected $_infoBlockType = \Magento\Paypal\Block\Payment\Info::class;

    /**
     * Controller for callback urls
     *
     * @var string
     * @since 2.0.0
     */
    protected $_callbackController = 'payflowadvanced';
}
