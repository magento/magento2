<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;

/**
 * Quantity modifier on CatalogInventory Product Editing Form
 */
class Quantity extends AbstractModifier
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
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * CatalogInventory constructor.
     * @param ArrayManager $arrayManager
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     */
    public function __construct(
        ArrayManager $arrayManager,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
    ) {
        $this->arrayManager = $arrayManager;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
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
        $stockQtyPath = $this->arrayManager->findPath('quantity_and_stock_status_qty', $meta, null, 'children');

        if (null === $stockQtyPath) {
            return $meta;
        }

        $product = $this->locator->getProduct();

        if ($this->isSourceItemsAllowedForProductType->execute($product->getTypeId()) === false) {
            return $meta;
        }

        if ($this->isSingleSourceMode->execute() === true) {
            $meta = $this->arrayManager->merge(
                $stockQtyPath . '/children/qty/arguments/data/config',
                $meta,
                [
                    'component' => 'Magento_InventoryCatalog/js/product/form/qty',
                ]
            );
        } else {
            $meta = $this->arrayManager->remove($stockQtyPath, $meta);
        }
        return $meta;
    }
}
