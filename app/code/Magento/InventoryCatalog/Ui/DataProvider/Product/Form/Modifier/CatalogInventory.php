<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Product form modifier.
 */
class CatalogInventory extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * CatalogInventory constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager

    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $stockStatusPath = $this->arrayManager->findPath('quantity_and_stock_status', $meta, null, 'children');

        if ($stockStatusPath) {
            $meta = $this->arrayManager->merge(
                $stockStatusPath . '/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalog/js/product/inventory/components/stock-status'
                ]
            );
        }

        $stockQtyPath = $this->arrayManager->findPath('quantity_and_stock_status_qty', $meta, null, 'children');

        if ($stockQtyPath) {
            $meta = $this->arrayManager->merge(
                $stockQtyPath . '/children/qty/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalog/js/product/inventory/components/qty',
                ]
            );
        }

        return $meta;
    }
}
