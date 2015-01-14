<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Admin User Repository
 *
 */
class AdminUser extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['admin_default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];
        $this->_data['user_with_sales_resource'] = $this->_getUserWithRole('sales_all_scopes');
    }

    /**
     * Build data for user
     *
     * @param string $roleName
     * @return array
     */
    protected function _getUserWithRole($roleName)
    {
        $role = [
            'data' => [
                'fields' => [
                    'roles' => [
                        'value' => ["%$roleName%"],
                    ],
                ],
            ],
        ];

        return array_replace_recursive($this->_data['admin_default'], $role);
    }
}
