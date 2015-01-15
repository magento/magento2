<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductTemplate
 * Data for creation Product Template
 */
class CatalogAttributeSet extends AbstractRepository
{
    /**
     * Construct
     *
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'attribute_set_name' => 'Default',
            'attribute_set_id' => 4,
        ];

        $this->_data['custom_attribute_set'] = [
            'attribute_set_name' => 'Custom_attribute_set%isolation%',
            'skeleton_set' => ['dataSet' => 'default'],
        ];

        $this->_data['custom_attribute_set_with_fpt'] = [
            'attribute_set_name' => 'Custom_attribute_set%isolation%',
            'skeleton_set' => ['dataSet' => 'default'],
            'assigned_attributes' => [
                'presets' => 'attribute_type_fpt',
            ],
        ];
    }
}
