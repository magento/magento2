<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Integration Repository
 */
class Integration extends AbstractRepository
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
        $this->_data['default_with_all_resources'] = [
            'name' => 'default_integration_%isolation%',
            'email' => 'test_%isolation%@example.com',
            'resource_access' => 'All',
            'resources' => [
                'Dashboard',
                'Sales',
                'Products',
                'Customers',
                'My Account',
                'Marketing',
                'Content',
                'Reports',
                'Stores',
                'System',
                'Global Search',
            ],
        ];
    }
}
