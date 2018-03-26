<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProductGraphQl\Model\Resolver\Products\Links;

use Magento\Catalog\Model\Product\Link;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Collection
 */
class Collection
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int[]
     */
    private $parentIds = [];

    /**
     * @var array
     */
    private $groupedLinkMap = [];

    /**
     * Add grouped parent id to collection filter.
     *
     * @param int $productId
     * @return void
     */
    public function addParentIdToFilter(int $productId) : void
    {
        if (!in_array($productId, $this->parentIds)) {
            $this->parentIds[] = $productId;
        }
    }

    /**
     * Get array of children association links for a passed in grouped product id
     *
     * @param int $productId
     * @return array|null
     */
    public function getGroupedLinksByParentId(int $productId) : ?array
    {
        $groupedLinks = $this->fetch();

        if (!isset($groupedLinks[$productId])) {
            return null;
        }

        return $groupedLinks[$productId];
    }

    /**
     * Fetch map of associated grouped products
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->parentIds) || !empty($this->groupedLinkMap)) {
            return $this->groupedLinkMap;
        }

        $collection = $this->link
            ->setLinkTypeId(\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED)
            ->getProductCollection();
        $collection
            ->setFlag('product_children', true)
            ->setIsStrongMode()
            ->addProductFilter($this->parentIds)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addFilterByRequiredOptions()
            ->setPositionOrder();

        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($collection as $item) {
            $this->groupedLinkMap[$item->getParentId()][$item->getId()]
                = [
                    'qty' => $item->getQty(),
                    'position' => (int)$item->getPosition(),
                    'sku' => $item->getSku()
                ];
        }

        return $this->groupedLinkMap;
    }
}
