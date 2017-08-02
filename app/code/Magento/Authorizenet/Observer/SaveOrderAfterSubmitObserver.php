<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Class \Magento\Authorizenet\Observer\SaveOrderAfterSubmitObserver
 *
 * @since 2.0.0
 */
class SaveOrderAfterSubmitObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $this->coreRegistry->register('directpost_order', $order, true);

        return $this;
    }
}
