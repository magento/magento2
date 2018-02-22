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
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;

/**
 * Product form modifier. Modify form stocks declaration
 */
class Stocks extends AbstractModifier
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
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param LocatorInterface $locator
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        LocatorInterface $locator,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->locator = $locator;
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
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            $meta['stocks'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'visible' => 0,
                        ],
                    ],
                ],
            ];
        }
        return $meta;
    }
}
