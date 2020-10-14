<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service add product to compare list
 */
class AddToCompareList
{
    /**
     * @var ItemFactory
     */
    private $compareItemFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Collection
     */
    private $itemCollection;

    /**
     * @param ItemFactory $compareItemFactory
     * @param ProductRepository $productRepository
     * @param Collection $collection
     */
    public function __construct(
        ItemFactory $compareItemFactory,
        ProductRepository $productRepository,
        Collection $collection
    ) {
        $this->compareItemFactory = $compareItemFactory;
        $this->productRepository = $productRepository;
        $this->itemCollection = $collection;
    }

    /**
     * Add products to compare list
     *
     * @param int $listId
     * @param array $products
     * @param int $storeId
     *
     * @return int
     */
    public function execute(int $listId, array $products, int $storeId): int
    {
        if (count($products)) {
            $existedProducts = $this->itemCollection->getProductsByListId($listId);
            foreach ($products as $productId) {
                if (!array_search($productId, $existedProducts)) {
                    if ($this->productExists($productId)) {
                        $item = $this->compareItemFactory->create();
                        $item->addProductData($productId);
                        $item->setStoreId($storeId);
                        $item->setListId($listId);
                        $item->save();
                    }
                }
            }
        }

        return (int)$listId;
    }

    /**
     * Check product exists.
     *
     * @param int $productId
     *
     * @return bool
     */
    private function productExists($productId)
    {
        try {
            $product = $this->productRepository->getById((int)$productId);
            return !empty($product->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
