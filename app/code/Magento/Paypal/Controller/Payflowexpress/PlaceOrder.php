<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflowexpress;

/**
 * Class \Magento\Paypal\Controller\Payflowexpress\PlaceOrder
 *
 * @since 2.0.0
 */
class PlaceOrder extends \Magento\Paypal\Controller\Express\AbstractExpress\PlaceOrder
{
    /**
     * Config mode type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_configType = \Magento\Paypal\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_configMethod = \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_checkoutType = \Magento\Paypal\Model\PayflowExpress\Checkout::class;
}
