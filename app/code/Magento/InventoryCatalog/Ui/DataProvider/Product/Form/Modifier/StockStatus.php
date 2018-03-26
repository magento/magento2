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
 * StockStatus modifier on CatalogInventory Product Editing Form
 */
class StockStatus extends AbstractModifier
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

        if (null === $stockStatusPath) {
            return $meta;
        }

        if ($this->isSingleSourceMode->execute() === true) {
            $meta = $this->arrayManager->merge(
                $stockStatusPath . '/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalog/js/product/form/stock-status',
                ]
            );
        } else {
            $meta = $this->arrayManager->remove($stockStatusPath, $meta);
        }
        return $meta;
    }
}
