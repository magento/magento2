<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Compare;

use Magento\Catalog\Model\CompareList\HashedListIdToListIdInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;


class ItemsFromListProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HashedListIdToListIdInterface
     */
    private $hashedListIdToListId;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Config $catalogConfig
     * @param StoreManagerInterface $storeManager
     * @param HashedListIdToListIdInterface $hashedListIdToListId
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Config $catalogConfig,
        StoreManagerInterface $storeManager,
        HashedListIdToListIdInterface $hashedListIdToListId
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->catalogConfig = $catalogConfig;
        $this->storeManager = $storeManager;
        $this->hashedListIdToListId = $hashedListIdToListId;
    }

    /**
     * @param int $customerId
     * @param string $hashedId
     */
    public function get(int $customerId, string $hashedId)
    {
        $collection = $this->collectionFactory->create();
        $collection->useProductItem(true)->setStoreId($this->storeManager->getStore()->getId());
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes());
        $collection->loadComparableAttributes();

        $catalogCompareListId = $this->hashedListIdToListId->execute($hashedId);

        if (0 !== $customerId) {
            $collection->setCatalogCompareListIdAndCustomerId($catalogCompareListId, $customerId);
        } else {
            $collection->setCatalogCompareListId($catalogCompareListId);
        }

        $items = [];
        foreach ($collection as $item) {
            $productData = $item->getData();
            $productData['model'] = $item;
            $items[] = [
                'item_id' => $item->getData('catalog_compare_item_id'),
                'product' => $productData
            ];
        }

        return $items;
    }
}
