<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * PayPal module observer
 * @since 2.0.0
 */
class SaveOrderAfterSubmitObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getData('order');
        $this->_coreRegistry->register('hss_order', $order, true);

        return $this;
    }
}
