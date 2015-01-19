<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class Attribute
 */
class ProductAttribute extends DataFixture
{
    /**
     * Logical sets for mapping data into tabs
     */
    const GROUP_PRODUCT_ATTRIBUTE_MAIN = 'product_attribute_tabs_main';
    const GROUP_PRODUCT_ATTRIBUTE_LABELS = 'product_attribute_tabs_labels';
    const GROUP_PRODUCT_ATTRIBUTE_FRONT = 'product_attribute_tabs_front';

    /**
     * Get attribute name
     *
     * @return string
     */
    public function getFrontendLabel()
    {
        return $this->getData('fields/frontend_label/value');
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->getData('fields/attribute_code/value');
    }

    /**
     * Get attribute option ids
     *
     * @return array
     */
    public function getOptionIds()
    {
        return $this->getData('fields/option_ids');
    }

    /**
     * Get attribute options labels
     *
     * @return array
     */
    public function getOptionLabels()
    {
        $options = $this->getOptions();
        $optionsLabels = [];
        foreach ($options as $option) {
            $optionsLabels[] = $option['label']['value'];
        }
        return $optionsLabels;
    }

    /**
     * Get attribute options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = $this->getData('options/value');
        return is_array($options) ? $options : [];
    }

    /**
     * Get attribute id
     *
     * @return string
     */
    public function getAttributeId()
    {
        return $this->getData('fields/attribute_id/value');
    }

    /**
     * Save Attribute into Magento
     */
    public function persist()
    {
        $attributeIds = Factory::getApp()->magentoCatalogCreateProductAttribute($this);
        $this->_data['fields']['attribute_id']['value'] = $attributeIds['attributeId'];
        $this->_data['fields']['option_ids'] = $attributeIds['optionIds'];

        return $this;
    }

    /**
     * {inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _initData()
    {
        $this->_data = [
            'fields' => [
                'attribute_code' => [
                    'value' => 'attribute_code_%isolation%',
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_MAIN,
                ],
                'frontend_label' => [
                    'value' => 'Attribute %isolation%',
                    'input_name' => 'frontend_label[0]',
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_MAIN,
                ],
                'frontend_input' => [
                    'value' => 'Dropdown',
                    'input' => 'select',
                    'input_value' => 'select',
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_MAIN,
                ],
                'is_configurable' => [
                    'value' => 'Yes',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_MAIN,
                ],
                'is_searchable' => [
                    'value' => 'Yes',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_FRONT,
                ],
                'is_visible_in_advanced_search' => [
                    'value' => 'Yes',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_FRONT,
                ],
                'is_comparable' => [
                    'value' => 'Yes',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_FRONT,
                ],
                'is_filterable' => [
                    'value' => 'Filterable (with results)',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_FRONT,
                ],
                'is_visible_on_front' => [
                    'value' => 'Yes',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_FRONT,
                ],
                'is_filterable_in_search' => [
                    'value' => 'Yes',
                    'input' => 'select',
                    'input_value' => 1,
                    'group' => self::GROUP_PRODUCT_ATTRIBUTE_FRONT,
                ],
            ],
            'options' => [
                'value' => [
                    'option_1' => [
                        'label' => [
                            'value' => 'Option 1 %isolation%',
                            'input_name' => 'option[value][option_0][0]',
                            'selector' => '//*[@id="manage-options-panel"]/table/tbody/tr[1]/td[3]/input',
                            'strategy' => Locator::SELECTOR_XPATH,
                        ],
                        'default' => [
                            'value' => 'Yes',
                            'input' => 'checkbox',
                            'input_name' => 'default[0]',
                            'input_value' => 'option_0',
                            'selector' => '//*[@id="manage-options-panel"]/table/tbody/tr[1]/td[2]/input',
                            'strategy' => Locator::SELECTOR_XPATH,
                        ],
                    ],
                    'option_2' => [
                        'label' => [
                            'value' => 'Option 2 %isolation%',
                            'input_name' => 'option[value][option_1][0]',
                            'selector' => '//*[@id="manage-options-panel"]/table/tbody/tr[2]/td[3]/input',
                            'strategy' => Locator::SELECTOR_XPATH,
                        ],
                        'default' => [
                            'value' => 'No',
                            'input' => 'checkbox',
                            'input_name' => 'default[1]',
                            'input_value' => 'option_1',
                            'selector' => '//*[@id="manage-options-panel"]/table/tbody/tr[2]/td[2]/input',
                            'strategy' => Locator::SELECTOR_XPATH,
                        ],
                    ],
                ],
            ],
        ];
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogProductAttribute($this->_dataConfig, $this->_data);
    }
}
