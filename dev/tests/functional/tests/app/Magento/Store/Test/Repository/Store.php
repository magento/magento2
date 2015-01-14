<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Store
 * Data for creation Store
 */
class Store extends AbstractRepository
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
            'group_id' => ['dataSet' => 'default'],
            'name' => 'Default Store View',
            'code' => 'base',
            'is_active' => 'Enabled',
            'store_id' => 1,
        ];

        $this->_data['custom'] = [
            'group_id' => ['dataSet' => 'default'],
            'name' => 'Custom_Store_%isolation%',
            'code' => 'code_%isolation%',
            'is_active' => 'Enabled',
        ];

        $this->_data['default_store_view'] = [
            'store_id' => 1,
            'name' => 'Default Store View',
        ];

        $this->_data['All Store Views'] = [
            'name' => 'All Store Views',
            'store_id' => 0,
        ];

        $this->_data['german'] = [
            'group_id' => ['dataSet' => 'default'],
            'name' => 'DE%isolation%',
            'code' => 'de%isolation%',
            'is_active' => 'Enabled',
        ];
    }
}
