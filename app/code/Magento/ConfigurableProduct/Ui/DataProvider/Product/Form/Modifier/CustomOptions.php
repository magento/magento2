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
use Magento\Ui\Component\Container;

/**
 * Data provider that customizes Customizable Options for Configurable product
 */
class CustomOptions extends AbstractModifier
{
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';
    const WARNING_PRICE_TYPE = 'price_type_warning';

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
            $meta = $this->addPriceTypeWarning($meta);
            $meta = $this->modifyPriceTypeOptions($meta);
        }

        return $meta;
    }

    /**
     * Add warning over options grid
     *
     * @param array $meta
     * @return array
     */
    private function addPriceTypeWarning(array $meta)
    {
        $gridPath = $this->arrayManager->findPath(
            CustomOptionsModifier::GRID_OPTIONS_NAME,
            $meta,
            CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME . '/children',
            'children'
        );

        if ($gridPath) {
            $path = $this->arrayManager->slicePath($gridPath, 0, -1) . '/' . static::WARNING_PRICE_TYPE;
            $sortOrder = $this->arrayManager->get($gridPath . static::META_CONFIG_PATH . '/sortOrder', $meta) - 1;

            $meta = $this->arrayManager->set(
                $path . static::META_CONFIG_PATH,
                $meta,
                [
                    'componentType' => Container::NAME,
                    'component' => 'Magento_Ui/js/form/components/html',
                    'additionalClasses' => 'message message-warning',
                    'sortOrder' => $sortOrder,
                    'content' => __(
                        'Custom options with price type "percent" is not available for configurable product.'
                    )
                ]
            );
        }

        return $meta;
    }

    /**
     * Remove price type "percent" from option list
     *
     * @param array $meta
     * @return array
     */
    private function modifyPriceTypeOptions(array $meta)
    {
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

        return $meta;
    }
}
