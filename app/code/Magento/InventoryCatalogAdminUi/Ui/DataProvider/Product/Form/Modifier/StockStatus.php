<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

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
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * CatalogInventory constructor.
     * @param ArrayManager $arrayManager
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     */
    public function __construct(
        ArrayManager $arrayManager,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->arrayManager = $arrayManager;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
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

        $product = $this->locator->getProduct();

        if ($this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            return $meta;
        }

        if ($this->isSingleSourceMode->execute() === true) {
            $meta = $this->arrayManager->merge(
                $stockStatusPath . '/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalogAdminUi/js/product/form/stock-status',
                ]
            );
        } else {
            $meta = $this->arrayManager->remove($stockStatusPath, $meta);
        }
        return $meta;
    }
}
