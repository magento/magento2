<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture\Admin;

use Mtf\Fixture\DataFixture;

/**
 * Class SuperAdmin
 *
 */
class SuperAdmin extends DataFixture
{
    /**
     * initialize data
     */
    protected function _initData()
    {
        $config = $this->_configuration->getConfigParam('application/backend_user_credentials');
        $this->_data = [
            'fields' => [
                'username' => [
                    'value' => $config['login'],
                ],
                'password' => [
                    'value' => $config['password'],
                ],
            ],
        ];
    }
}
