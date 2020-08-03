<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;

/**
 * Verifies Stock item model changes.
 */
class StockItemChecker
{
    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * @var string[]
     */
    private $skippedAttributes;

    /**
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param ArrayUtils $arrayUtils
     * @param string[] $skippedAttributes
     */
    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        ArrayUtils $arrayUtils,
        array $skippedAttributes = []
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->arrayUtils = $arrayUtils;
        $this->skippedAttributes = $skippedAttributes;
    }

    /**
     * Check if stock item is modified.
     *
     * @param StockItem $model
     * @return bool
     */
    public function isModified($model): bool
    {
        if (!$model->getId()) {
            return true;
        }
        $stockItem = $this->stockItemRepository->get($model->getId());
        $stockItemData = $stockItem->getData();
        $modelData = $model->getData();
        foreach ($this->skippedAttributes as $attribute) {
            unset($stockItemData[$attribute], $modelData[$attribute]);
        }
        $diff = $this->arrayUtils->recursiveDiff($stockItemData, $modelData);

        return !empty($diff);
    }
}
