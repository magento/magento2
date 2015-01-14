<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductAttribute
 * Data for creation Product Attributes
 */
class CatalogProductAttribute extends AbstractRepository
{
    /**
     * Construct
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['attribute_type_text_field'] = [
            'frontend_label' => 'attribute_text%isolation%',
            'attribute_code' => 'attribute_text%isolation%',
            'frontend_input' => 'Text Field',
            'is_required' => 'No',
        ];

        $this->_data['attribute_type_dropdown'] = [
            'frontend_label' => 'attribute_dropdown%isolation%',
            'attribute_code' => 'attribute_dropdown%isolation%',
            'frontend_input' => 'Dropdown',
            'is_required' => 'No',
            'is_configurable' => 'Yes',
            'options' => [
                [
                    'is_default' => 'Yes',
                    'admin' => 'black',
                    'view' => 'option_0_%isolation%',
                ],
                [
                    'is_default' => 'No',
                    'admin' => 'white',
                    'view' => 'option_1_%isolation%',
                ],
                [
                    'is_default' => 'No',
                    'admin' => 'green',
                    'view' => 'option_2_%isolation%',
                ],
            ],
        ];

        $this->_data['attribute_type_dropdown_two_options'] = [
            'frontend_label' => 'attribute_dropdown%isolation%',
            'attribute_code' => 'attribute_dropdown%isolation%',
            'frontend_input' => 'Dropdown',
            'is_required' => 'No',
            'is_configurable' => 'Yes',
            'options' => [
                [
                    'is_default' => 'Yes',
                    'admin' => 'black',
                    'view' => 'option_0_%isolation%',
                ],
                [
                    'is_default' => 'No',
                    'admin' => 'white',
                    'view' => 'option_1_%isolation%',
                ],
            ],
        ];

        $this->_data['attribute_type_dropdown_one_option'] = [
            'frontend_label' => 'attribute_dropdown%isolation%',
            'attribute_code' => 'attribute_dropdown%isolation%',
            'frontend_input' => 'Dropdown',
            'is_required' => 'No',
            'is_configurable' => 'Yes',
            'options' => [
                [
                    'is_default' => 'Yes',
                    'admin' => 'black',
                    'view' => 'option_0_%isolation%',
                ],
            ],
        ];

        $this->_data['color'] = [
            'frontend_label' => 'color_%isolation%',
            'attribute_code' => 'color_%isolation%',
            'frontend_input' => 'Dropdown',
            'is_required' => 'No',
            'is_configurable' => 'Yes',
            'options' => [
                [
                    'is_default' => 'Yes',
                    'admin' => 'black',
                    'view' => 'black_%isolation%',
                ],
                [
                    'is_default' => 'No',
                    'admin' => 'white',
                    'view' => 'white_%isolation%',
                ],
            ],
        ];

        $this->_data['size'] = [
            'frontend_label' => 'size_%isolation%',
            'attribute_code' => 'size_%isolation%',
            'frontend_input' => 'Dropdown',
            'is_required' => 'No',
            'is_configurable' => 'Yes',
            'options' => [
                [
                    'is_default' => 'Yes',
                    'admin' => 'xl',
                    'view' => 'xl_%isolation%',
                ],
                [
                    'is_default' => 'No',
                    'admin' => 'xxl',
                    'view' => 'xxl_%isolation%',
                ],
            ],
        ];

        $this->_data['attribute_type_fpt'] = [
            'frontend_label' => 'fpt_%isolation%',
            'frontend_input' => 'Fixed Product Tax',
        ];
    }
}
