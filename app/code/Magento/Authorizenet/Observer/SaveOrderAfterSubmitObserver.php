<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SaveOrderAfterSubmitObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
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
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $this->coreRegistry->register('directpost_order', $order, true);

        return $this;
    }
}
