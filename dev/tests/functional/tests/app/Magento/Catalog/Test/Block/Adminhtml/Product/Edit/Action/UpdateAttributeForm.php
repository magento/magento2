<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Product update Attributes Form.
 */
class UpdateAttributeForm extends FormTabs
{
    /**
     * Checkbox array mapping.
     *
     * @var array
     */
    private $checkboxMapping = [
        'attributes' => [
            'price' => 'toggle_price'
        ],
        'advanced_inventory' => [
            'stock_data' => 'stock_data_checkbox'
        ]
    ];

    /**
     * Create data array for filling containers.
     *
     * Returns data in format
     * [[abstract_container_name => [field_name => [attribute_name => attribute_value, ..], ..], ..]
     * where container name should be set to 'null' if a field is not present on the form.
     *
     * @param InjectableFixture $fixture
     * @return array
     */
    protected function getFixtureFieldsByContainers(InjectableFixture $fixture)
    {
        $dataByContainer = [];
        $data = $fixture->getData();

        foreach ($this->containers as $key => $container) {
            foreach ($container['fields'] as $fieldKey => $field) {
                if (isset($data[$fieldKey])) {
                    $dataByContainer[$key][$fieldKey]['value'] = $data[$fieldKey];
                    if (isset($this->checkboxMapping[$key][$fieldKey])) {
                        $dataByContainer[$key][$this->checkboxMapping[$key][$fieldKey]]['value'] = 'Yes';
                    }
                }
            }
            if (isset($dataByContainer[$key])) {
                $dataByContainer[$key] = array_reverse($dataByContainer[$key]);
            }
        }

        return $dataByContainer;
    }
}
