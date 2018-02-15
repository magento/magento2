<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;
use Magento\Ui\Component\Form;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InventoryConfigurablePanel extends AbstractModifier
{
    const RECORD = 'record';
    const QUANTITY_CONTAINER = 'quantity_container';

    /**
     * {@inheritdoc}
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
    private function getQuantityContainerConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'text',
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'template' => 'ui/form/field',
                        'dataScope' => 'qty',
                        'label' => __('Quantity'),
                        'formElement' => Form\Element\Input::NAME,
                        'elementTmpl' => 'Magento_InventoryConfigurableProduct/dynamic-rows/cells/cell-source'
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $children = 'children';
        $meta[ConfigurablePanel::GROUP_CONFIGURABLE][$children]
            [ConfigurablePanel::CONFIGURABLE_MATRIX][$children]
            [static::RECORD][$children][static::QUANTITY_CONTAINER] = $this->getQuantityContainerConfig();

        return $meta;
    }
}
