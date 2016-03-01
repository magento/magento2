<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

/**
 * Data provider that customizes Customizable Options for Configurable product
 */
class CustomOptions extends AbstractModifier
{
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(LocatorInterface $locator, ArrayManager $arrayManager)
    {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
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
        if ($this->locator->getProduct()->getTypeId() === static::PRODUCT_TYPE_CONFIGURABLE) {
            $paths = $this->arrayManager->findPaths(
                CustomOptionsModifier::FIELD_PRICE_TYPE_NAME,
                $meta,
                CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME . '/children',
                'children'
            );

            foreach ($paths as $fieldPath) {
                $optionsPath = $fieldPath . static::META_CONFIG_PATH . '/options';
                $options = $this->arrayManager->get($optionsPath, $meta);

                if ($options) {
                    foreach ($options as $index => $option) {
                        if ($option['value'] === 'percent') {
                            unset($options[$index]);
                        }
                    }

                    $meta = $this->arrayManager->replace($optionsPath, $meta, $options);
                }
            }
        }

        return $meta;
    }
}
