<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture\Admin;

use Magento\Mtf\Fixture\DataFixture;

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
        $this->_data = [
            'fields' => [
                'username' => [
                    'value' => $this->_configuration->get('application/0/backendLogin/0/value'),
                ],
                'password' => [
                    'value' => $this->_configuration->get('application/0/backendPassword/0/value'),
                ],
            ],
        ];
    }
}
