<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory as AdvancedInventoryModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;

/**
 * Hide qty and is_in_stock fields in Advanced Inventory panel
 */
class AdvancedInventory extends AbstractModifier
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
        if ($this->isSingleSourceMode->execute()) {
            return $meta;
        }

        $stockDataPath = $this->arrayManager->findPath(
            AdvancedInventoryModifier::STOCK_DATA_FIELDS,
            $meta,
            null,
            'children'
        );
        if (null === $stockDataPath) {
            return $meta;
        }

        $qtyPath = $stockDataPath . '/children/qty/arguments/data/config';
        $meta = $this->arrayManager->merge(
            $qtyPath,
            $meta,
            [
                'visible' => 0,
                'imports' => '',
            ]
        );

        $stockStatusPath = $stockDataPath . '/children/container_is_in_stock/arguments/data/config';
        $meta = $this->arrayManager->set(
            $stockStatusPath,
            $meta,
            [
                'visible' => 0,
                'imports' => '',
            ]
        );

        return $meta;
    }
}
