<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Class \Magento\Authorizenet\Observer\UpdateAllEditIncrementsObserver
 *
 * @since 2.0.0
 */
class UpdateAllEditIncrementsObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Authorizenet\Helper\Data
     * @since 2.0.0
     */
    protected $authorizenetData;

    /**
     * @param \Magento\Authorizenet\Helper\Data $authorizenetData
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Authorizenet\Helper\Data $authorizenetData
    ) {
        $this->authorizenetData = $authorizenetData;
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
        $this->authorizenetData->updateOrderEditIncrements($order);

        return $this;
    }
}
