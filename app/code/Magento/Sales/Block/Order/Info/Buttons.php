<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block of links in Order view page
 */
namespace Magento\Sales\Block\Order\Info;

use Magento\Customer\Model\Context;

/**
 * @api
 * @since 2.0.0
 */
class Buttons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'order/info/buttons.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Get url for printing order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @since 2.0.0
     */
    public function getPrintUrl($order)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('sales/guest/print', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('sales/order/print', ['order_id' => $order->getId()]);
    }

    /**
     * Get url for reorder action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @since 2.0.0
     */
    public function getReorderUrl($order)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('sales/guest/reorder', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }
}
