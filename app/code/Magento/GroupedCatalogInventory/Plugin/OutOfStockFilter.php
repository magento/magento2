<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GroupedCatalogInventory\Plugin;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Removes out of stock products from cart candidates when appropriate
 */
class OutOfStockFilter
{
    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $criteriaInterfaceFactory;

    /**
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusCriteriaInterfaceFactory $criteriaInterfaceFactory
     */
    public function __construct(
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusCriteriaInterfaceFactory $criteriaInterfaceFactory
    ) {
        $this->stockStatusRepository = $stockStatusRepository;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
    }

    /**
     * Removes out of stock products for requests that don't specify the super group
     *
     * @param Grouped $subject
     * @param array|string $result
     * @param \Magento\Framework\DataObject $buyRequest
     * @return string|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareForCartAdvanced(
        Grouped $subject,
        $result,
        \Magento\Framework\DataObject $buyRequest
    ) {
        if (!is_array($result) && $result instanceof Product) {
            $result = [$result];
        }

        // Only remove out-of-stock products if no quantities were specified
        if (is_array($result) && !empty($result) && !$buyRequest->getData('super_group')) {
            $productIds = [];
            $productIdMap = [];

            foreach ($result as $index => $cartItem) {
                $productIds[] = $cartItem->getId();
                $productIdMap[$cartItem->getId()] = $index;
            }

            $criteria = $this->criteriaInterfaceFactory->create();
            $criteria->setProductsFilter($productIds);

            $stockStatusCollection = $this->stockStatusRepository->getList($criteria);
            foreach ($stockStatusCollection->getItems() as $status) {
                /** @var $status StockStatusInterface */
                if ($status->getStockStatus() == StockStatusInterface::STATUS_OUT_OF_STOCK) {
                    unset($result[$productIdMap[$status->getProductId()]]);
                }
            }

            unset($productIdMap);
        }

        return $result;
    }
}
