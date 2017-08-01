<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

/**
 * Class \Magento\Checkout\Block\Success
 *
 * @since 2.0.0
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getRealOrderId()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_orderFactory->create()->load($this->getLastOrderId());
        return $order->getIncrementId();
    }
}
