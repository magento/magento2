<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Ui\Component\Container;

/**
 * Data provider that customizes Customizable Options for Configurable product
 */
class CustomOptions extends AbstractModifier
{
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
        $meta = $this->addPriceTypeWarning($meta);
        $meta = $this->modifyPriceTypeFields($meta);

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
            $isConfigurable = $this->locator->getProduct()->getTypeId() === ConfigurableProductType::TYPE_CODE;

            $meta = $this->arrayManager->set(
                $path . static::META_CONFIG_PATH,
                $meta,
                [
                    'componentType' => Container::NAME,
                    'component' => 'Magento_ConfigurableProduct/js/components/custom-options-warning',
                    'additionalClasses' => 'message message-warning',
                    'sortOrder' => $sortOrder,
                    'isConfigurable' => $isConfigurable,
                    'content' => __(
                        'Custom options with price type "percent" is not available for configurable product.'
                    ),
                    'imports' => [
                        'updateVisibility' => 'ns = ${ $.ns }, index = '
                            . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty'
                    ]
                ]
            );
        }

        return $meta;
    }

    /**
     * Modify "Price Type" fields
     *
     * @param array $meta
     * @return array
     */
    private function modifyPriceTypeFields(array $meta)
    {
        $isConfigurable = $this->locator->getProduct()->getTypeId() === ConfigurableProductType::TYPE_CODE;
        $paths = $this->arrayManager->findPaths(
            CustomOptionsModifier::FIELD_PRICE_TYPE_NAME,
            $meta,
            CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME . '/children',
            'children'
        );

        foreach ($paths as $fieldPath) {
            $meta = $this->arrayManager->merge(
                $fieldPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'component' => 'Magento_ConfigurableProduct/js/components/custom-options-price-type',
                    'isConfigurable' => $isConfigurable,
                    'bannedOptions' => ['percent'],
                    'imports' => [
                        'updateOptions' => 'ns = ${ $.ns }, index = '
                            . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty'
                    ]
                ]
            );
        }

        return $meta;
    }
}
