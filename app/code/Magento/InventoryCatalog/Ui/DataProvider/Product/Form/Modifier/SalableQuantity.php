<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalog\Model\GetSalableQuantityDataBySku;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;

/**
 * Product form modifier. Modify form stocks declaration
 */
class SalableQuantity extends AbstractModifier
{
    /**
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param LocatorInterface $locator
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     */
    public function __construct(
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        LocatorInterface $locator,
        IsSingleSourceModeInterface $isSingleSourceMode,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->locator = $locator;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false
            || null === $product->getId()
        ) {
            return $data;
        }

        $data[$product->getId()]['salable_quantity'] = $this->getSalableQuantityDataBySku->execute($product->getSku());
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false
            || null === $product->getId()
        ) {
            return $meta;
        }

        $meta['salable_quantity'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => 1,
                    ],
                ],
            ],
        ];
        return $meta;
    }
}
