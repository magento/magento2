<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item as CompareItemResource;
use Magento\Catalog\Model\Product\Compare\ItemFactory;

/**
 *  Remove product from compare list
 */
class RemoveFromCompareList
{
    /**
     * @var ItemFactory
     */
    private $compareItemFactory;

    /**
     * @var CompareItemResource
     */
    private $compareItemResource;

    /**
     * @param ItemFactory $compareItemFactory
     * @param CompareItemResource $compareItemResource
     */
    public function __construct(
        ItemFactory $compareItemFactory,
        CompareItemResource $compareItemResource
    ) {
        $this->compareItemFactory = $compareItemFactory;
        $this->compareItemResource = $compareItemResource;
    }

    /**
     * Remove products from compare list
     *
     * @param int $listId
     * @param array $products
     */
    public function execute(int $listId, array $products)
    {
        foreach ($products as $productId) {
            /* @var $item Item */
            $item = $this->compareItemFactory->create();
            $item->setListId($listId);
            $this->compareItemResource->loadByProduct($item, $productId);
            if ($item->getId()) {
                $this->compareItemResource->delete($item);
            }
        }
    }
}
