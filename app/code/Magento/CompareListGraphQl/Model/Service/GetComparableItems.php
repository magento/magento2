<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Block\Product\Compare\ListCompare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CompareListGraphQl\Model\Service\Collection\GetComparableItemsCollection as ComparableItemsCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Get comparable products
 */
class GetComparableItems
{
    /**
     * @var ListCompare
     */
    private $blockListCompare;

    /**
     * @var ComparableItemsCollection
     */
    private $comparableItemsCollection;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ListCompare $listCompare
     * @param ComparableItemsCollection $comparableItemsCollection
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ListCompare $listCompare,
        ComparableItemsCollection $comparableItemsCollection,
        ProductRepository $productRepository
    ) {
        $this->blockListCompare = $listCompare;
        $this->comparableItemsCollection = $comparableItemsCollection;
        $this->productRepository = $productRepository;
    }

    /**
     * Get comparable items
     *
     * @param int $listId
     * @param ContextInterface $context
     *
     * @return array
     * @throws GraphQlInputException
     */
    public function execute(int $listId, ContextInterface $context)
    {
        $items = [];
        foreach ($this->comparableItemsCollection->execute($listId, $context) as $item) {
            /** @var Product $item */
            $items[] = [
                'uid' => $item->getId(),
                'product' => $this->getProductData((int)$item->getId()),
                'attributes' => $this->getProductComparableAttributes($listId, $item, $context)
            ];
        }

        return $items;
    }

    /**
     * Get product data
     *
     * @param int $productId
     *
     * @return array
     *
     * @throws GraphQlInputException
     */
    private function getProductData(int $productId): array
    {
        $productData = [];
        try {
            $item = $this->productRepository->getById($productId);
            $productData = $item->getData();
            $productData['model'] = $item;
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $productData;
    }

    /**
     * Get comparable attributes for product
     *
     * @param int $listId
     * @param Product $product
     * @param ContextInterface $context
     *
     * @return array
     */
    private function getProductComparableAttributes(int $listId, Product $product, ContextInterface $context): array
    {
        $attributes = [];
        $itemsCollection = $this->comparableItemsCollection->execute($listId, $context);
        foreach ($itemsCollection->getComparableAttributes() as $item) {
            $attributes[] = [
                'code' =>  $item->getAttributeCode(),
                'value' => $this->blockListCompare->getProductAttributeValue($product, $item)
            ];
        }

        return $attributes;
    }
}
