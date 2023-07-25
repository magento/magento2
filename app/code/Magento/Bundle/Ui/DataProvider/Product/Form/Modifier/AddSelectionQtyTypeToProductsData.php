<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\CatalogInventory\Model\StockRegistryPreloader;

/**
 * Affects Qty field for newly added selection
 */
class AddSelectionQtyTypeToProductsData implements ModifierInterface
{
    /**
     * @var StockRegistryPreloader
     */
    private StockRegistryPreloader $stockRegistryPreloader;

    /**
     * Initializes dependencies
     *
     * @param StockRegistryPreloader $stockRegistryPreloader
     */
    public function __construct(StockRegistryPreloader $stockRegistryPreloader)
    {
        $this->stockRegistryPreloader = $stockRegistryPreloader;
    }

    /**
     * Modify Meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Modify Data - checks if new selection can have decimal quantity
     *
     * @param array $data
     * @return array
     * @throws NoSuchEntityException
     */
    public function modifyData(array $data): array
    {
        $productIds = array_column($data['items'], 'entity_id');

        $stockItems = [];
        if ($productIds) {
            $stockItems = $this->stockRegistryPreloader->preloadStockItems($productIds);
        }

        $isQtyDecimals = [];
        foreach ($stockItems as $stockItem) {
            $isQtyDecimals[$stockItem->getProductId()] = $stockItem->getIsQtyDecimal();
        }
        
        foreach ($data['items'] as &$item) {
            if (isset($isQtyDecimals[$item['entity_id']])) {
                $item['selection_qty_is_integer'] = !$isQtyDecimals[$item['entity_id']];
            }
        }

        return $data;
    }
}
