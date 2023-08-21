<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Additional logic on how to display the layout update field.
 */
class LayoutUpdate implements ModifierInterface
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Extract custom layout value.
     *
     * @param ProductInterface|Product $product
     * @return mixed
     */
    private function extractLayoutUpdate(ProductInterface $product)
    {
        if ($product instanceof Product && !$product->hasData(Product::CUSTOM_ATTRIBUTES)) {
            return $product->getData('custom_layout_update');
        }

        $attr = $product->getCustomAttribute('custom_layout_update');

        return $attr ? $attr->getValue() : null;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        if ($this->extractLayoutUpdate($product)) {
            $data[$product->getId()][AbstractModifier::DATA_SOURCE_DEFAULT]['custom_layout_update_file']
                = \Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate::VALUE_USE_UPDATE_XML;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
