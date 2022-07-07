<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model\Sales\Order\Link;

use Magento\Downloadable\Model\Link\Purchased as PurchasedEntity;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\DataObject;

/**
 * Order purchased link resolver
 */
class Purchased
{
    /**
     * @var PurchasedFactory
     */
    private $linkPurchasedFactory;
    /**
     * @var CollectionFactory
     */
    private $linkPurchasedItemCollectionFactory;

    /**
     * @param PurchasedFactory $linkPurchasedFactory
     * @param CollectionFactory $linkPurchasedItemCollectionFactory
     */
    public function __construct(
        PurchasedFactory $linkPurchasedFactory,
        CollectionFactory $linkPurchasedItemCollectionFactory
    ) {
        $this->linkPurchasedFactory = $linkPurchasedFactory;
        $this->linkPurchasedItemCollectionFactory = $linkPurchasedItemCollectionFactory;
    }

    /**
     * Get order purchased link
     *
     * @param DataObject $item
     * @return PurchasedEntity
     */
    public function getLink(DataObject $item): PurchasedEntity
    {
        if ($item->getOrderItem()) {
            $item = $item->getOrderItem();
        }

        if ($item->getProductType() !== Type::TYPE_DOWNLOADABLE) {
            $childrenItems = $item->getChildrenItems() ?: [];
            if (count($childrenItems) === 1) {
                $childItem = reset($childrenItems);
                if ($childItem->getProductType() == Type::TYPE_DOWNLOADABLE) {
                    $item = $childItem;
                }
            }
        }
        $itemId = $item->getId();

        $purchased = $this->linkPurchasedFactory->create()
            ->load($itemId, 'order_item_id');
        $purchasedLinks = $this->linkPurchasedItemCollectionFactory->create()
            ->addFieldToFilter('order_item_id', $itemId);
        $purchased->setPurchasedItems($purchasedLinks);

        return $purchased;
    }
}
