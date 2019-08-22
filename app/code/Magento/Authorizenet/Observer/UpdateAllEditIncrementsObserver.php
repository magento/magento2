<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Class UpdateAllEditIncrementsObserver
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class UpdateAllEditIncrementsObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Authorizenet\Helper\Data
     */
    protected $authorizenetData;

    /**
     * @param \Magento\Authorizenet\Helper\Data $authorizenetData
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
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $this->authorizenetData->updateOrderEditIncrements($order);

        return $this;
    }
}
