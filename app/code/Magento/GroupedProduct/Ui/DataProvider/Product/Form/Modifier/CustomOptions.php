<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

/**
 * Data provider that customizes Customizable Options for Grouped product
 * @since 2.1.0
 */
class CustomOptions extends AbstractModifier
{
    const PRODUCT_TYPE_GROUPED = 'grouped';

    /**
     * @var LocatorInterface
     * @since 2.1.0
     */
    private $locator;

    /**
     * @var ArrayManager
     * @since 2.1.0
     */
    private $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @since 2.1.0
     */
    public function __construct(LocatorInterface $locator, ArrayManager $arrayManager)
    {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($product->getTypeId() === static::PRODUCT_TYPE_GROUPED) {
            $data = $this->arrayManager->remove(
                $this->arrayManager->findPath(CustomOptionsModifier::FIELD_ENABLE, $data),
                $data
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === static::PRODUCT_TYPE_GROUPED) {
            $meta = $this->arrayManager->remove(CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME, $meta);
        }

        return $meta;
    }
}
