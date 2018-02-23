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
class QuantityAndStockStatus extends AbstractModifier
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
            $meta = $this->arrayManager->merge(
                $stockStatusPath . '/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalog/js/product/form/stock-status'
                ]
            );
        }

        $stockQtyPath = $this->arrayManager->findPath('quantity_and_stock_status_qty', $meta, null, 'children');

        if ($stockQtyPath) {
            $meta = $this->arrayManager->merge(
                $stockQtyPath . '/children/qty/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalog/js/product/form/qty',
                ]
            );
        }

        //TODO: What we should do with Advanced Inventory??
        if ($this->isSingleSourceMode->execute() === false) {
            unset($meta['product-details']['children']['container_quantity_and_stock_status']);
            // or unset($meta['product-details']['children']['quantity_and_stock_status_qty']['children']['qty']);
            unset($meta['product-details']['children']['quantity_and_stock_status_qty']);
        }

        return $meta;
    }
}
