<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Product form modifier. Modify form stocks declaration
 */
class SalableQuantity extends AbstractModifier
{
    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

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
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param LocatorInterface $locator
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     */
    public function __construct(
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        LocatorInterface $locator,
        IsSingleSourceModeInterface $isSingleSourceMode,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
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

        if ($this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false
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

        if ($this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false
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
