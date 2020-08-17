<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Data provider for advanced inventory modal form.
 */
class AdvancedInventoryModal extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var array
     */
    private $meta;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->prepareMeta();

        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Modify Advanced Inventory Modal meta.
     *
     * @return array
     */
    private function prepareMeta(): array
    {
        $product = $this->locator->getProduct();
        $readOnly = (bool)$product->getInventoryReadonly();

        $this->meta['advanced_inventory_modal']['children']['stock_data']['arguments']
        ['data']['config']['disabled'] = $readOnly;

        return $this->meta;
    }
}
