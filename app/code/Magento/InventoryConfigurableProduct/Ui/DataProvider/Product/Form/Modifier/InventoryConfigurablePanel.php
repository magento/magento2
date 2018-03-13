<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\AllowedProductTypes;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;
use Magento\Ui\Component\Form;

/**
 * Data provider for Configurable panel.
 */
class InventoryConfigurablePanel extends AbstractModifier
{
    const RECORD = 'record';
    const QUANTITY_CONTAINER = 'quantity_container';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var AllowedProductTypes
     */
    private $allowedProductTypes;

    /**
     * @param LocatorInterface $locator
     * @param AllowedProductTypes $allowedProductTypes
     */
    public function __construct(
        LocatorInterface $locator,
        AllowedProductTypes $allowedProductTypes
    ) {
        $this->locator = $locator;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Composes configuration for "quantity_container" component.
     *
     * @return array
     */
    private function getQuantityContainerConfig(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'text',
                        'component' => 'Magento_InventoryConfigurableProduct/js/form/element/quantity',
                        'template' => 'ui/form/field',
                        'dataScope' => 'qty',
                        'label' => __('Quantity'),
                        'formElement' => Form\Element\Input::NAME
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if ($this->allowedProductTypes->isAllowedProductType($this->locator->getProduct())) {
            $matrix = $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children'][ConfigurablePanel::CONFIGURABLE_MATRIX];

            $matrix['children'][static::RECORD]['children'][static::QUANTITY_CONTAINER]
                = $this->getQuantityContainerConfig();

            $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children'][ConfigurablePanel::CONFIGURABLE_MATRIX] = $matrix;
        }

        return $meta;
    }
}
