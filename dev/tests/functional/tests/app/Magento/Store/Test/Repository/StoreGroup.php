<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class StoreGroup
 * Data for creation Store Group
 */
class StoreGroup extends AbstractRepository
{
    /**
     * @constructor
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'website_id' => [
                'dataSet' => 'main_website',
            ],
            'name' => 'Main Website Store',
            'group_id' => 1,
            'root_category_id' => [
                'dataSet' => 'default_category',
            ],
        ];

        $this->_data['custom'] = [
            'website_id' => [
                'dataSet' => 'main_website',
            ],
            'name' => 'store_name_%isolation%',
            'root_category_id' => [
                'dataSet' => 'default_category',
            ],
        ];
    }
}
