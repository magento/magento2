<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Observer;

use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection as PurchasedCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Assign Downloadable links to customer created after issuing guest order.
 */
class UpdateLinkPurchasedObserver implements ObserverInterface
{
    /**
     * Purchased links collection factory
     *
     * @var CollectionFactory
     */
    private $purchasedCollectionFactory;

    /**
     * @param CollectionFactory $purchasedCollectionFactory
     */
    public function __construct(
        CollectionFactory $purchasedCollectionFactory
    ) {
        $this->purchasedCollectionFactory = $purchasedCollectionFactory;
    }

    /**
     * Link customer_id to downloadable link purchased after update order
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        $customerId = $order->getCustomerId();
        if (!$orderId || !$customerId) {
            return $this;
        }
        $purchasedLinksCollection = $this->getPurchasedCollection((int)$orderId);
        foreach ($purchasedLinksCollection as $linkPurchased) {
            $linkPurchased->setCustomerId($customerId)->save();
        }

        return $this;
    }

    /**
     * Get purchased collection by order id
     *
     * @param int $orderId
     * @return PurchasedCollection
     */
    private function getPurchasedCollection(int $orderId): PurchasedCollection
    {
        return $this->purchasedCollectionFactory->create()->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        );
    }
}
