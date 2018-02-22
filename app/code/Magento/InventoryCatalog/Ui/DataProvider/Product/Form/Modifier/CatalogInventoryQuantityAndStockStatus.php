<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;

/**
 * Quantity And StockStatus modifier on CatalogInventory Product Editing Form
 */
class CatalogInventoryQuantityAndStockStatus extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * CatalogInventory constructor.
     * @param ArrayManager $arrayManager
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        ArrayManager $arrayManager,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->arrayManager = $arrayManager;
        $this->isSingleSourceMode = $isSingleSourceMode;
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
            if ($this->isSingleSourceMode->execute()) {
                $meta = $this->arrayManager->merge(
                    $stockStatusPath . '/arguments/data/config',
                    $meta,
                    [
                        'component' => 'Magento_InventoryCatalog/js/product/inventory/components/stock-status',
                    ]
                );
            } else {
                $meta = $this->arrayManager->merge(
                    $stockStatusPath . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false,
                        'imports' => ''
                    ]
                );
            }
        }

        $stockQtyPath = $this->arrayManager->findPath('quantity_and_stock_status_qty', $meta, null, 'children');

        if ($stockQtyPath) {
            if ($this->isSingleSourceMode->execute()) {
                $meta = $this->arrayManager->merge(
                    $stockQtyPath . '/children/qty/arguments/data/config',
                    $meta,
                    [
                        'component' => 'Magento_InventoryCatalog/js/product/inventory/components/qty'
                    ]
                );
            } else {
                $meta = $this->arrayManager->merge(
                    $stockQtyPath . '/arguments/data/config',
                    $meta,
                    ['visible' => false]
                );
            }
        }

        return $meta;
    }
}
