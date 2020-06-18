<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Downloadable\Model;

use Magento\Downloadable\Model\ResourceModel\Link\Purchased as PurchasedResource;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory;

/**
 * Delete records from downloadable_link_purchased associated with provided order
 */
class RemoveLinkPurchasedByOrderIncrementId
{
    /** @var CollectionFactory */
    private $linkCollectionFactory;

    /** @var PurchasedResource */
    private $purchasedResource;

    /**
     * @param CollectionFactory $linkCollectionFactory
     * @param PurchasedResource $purchasedResource
     */
    public function __construct(CollectionFactory $linkCollectionFactory, PurchasedResource $purchasedResource)
    {
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->purchasedResource = $purchasedResource;
    }

    /**
     * Remove records from downloadable_link_purchased related to provided order
     *
     * @param string $orderIncrementId
     * @return void
     */
    public function execute(string $orderIncrementId): void
    {
        $collection = $this->linkCollectionFactory->create();
        $collection->addFieldToFilter('order_increment_id', $orderIncrementId);
        foreach ($collection as $item) {
            $this->purchasedResource->delete($item);
        }
    }
}
