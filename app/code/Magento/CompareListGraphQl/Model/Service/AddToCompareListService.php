<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service add product to compare list
 */
class AddToCompareListService
{
    /**
     * Compare item factory
     *
     * @var ItemFactory
     */
    private $compareItemFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ItemFactory $compareItemFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ItemFactory $compareItemFactory,
        ProductRepository $productRepository
    ) {
        $this->compareItemFactory = $compareItemFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Add to compare list
     *
     * @param int $listId
     * @param array $items
     */
    public function addToCompareList(int $listId, array $items)
    {
        foreach ($items['items'] as $key) {
            /* @var $item Item */
            $item = $this->compareItemFactory->create();
            $item->loadByProduct($key);
            if (!$item->getId() && $this->productExists($key)) {
                $item->addProductData($key);
                $item->setListId($listId);
                $item->save();
            }
        }
    }

    /**
     * Check product exists.
     *
     * @param int|Product $product
     *
     * @return bool
     */
    private function productExists($product)
    {
        if ($product instanceof Product && $product->getId()) {
            return true;
        }

        try {
            $product = $this->productRepository->getById((int)$product);
            return !empty($product->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
