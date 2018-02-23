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
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->isSingleSourceMode->execute()) {
            return $meta;
        }

        $advancedInventoryPath = $this->arrayManager->findPath('advanced_inventory_modal', $meta, null, 'children');

        if ($advancedInventoryPath) {
            $meta = $this->arrayManager->merge(
                $advancedInventoryPath . '/children/stock_data/children/qty/arguments/data/config',
                $meta,
                [
                    'visible' => false,
                    'imports' => ''
                ]
            );

            $isInStockContainerPath = $advancedInventoryPath .
                '/children/stock_data/children/container_is_in_stock/children/is_in_stock/arguments/data/config';

            $meta = $this->arrayManager->set(
                $isInStockContainerPath,
                $meta,
                ['visible' => false]
            );
        }

        return $meta;
    }
}
