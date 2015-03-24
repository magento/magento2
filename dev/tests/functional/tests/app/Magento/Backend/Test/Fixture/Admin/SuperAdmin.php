<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
                    'value' => $this->_configuration->getParameter('application/backendLogin'),
                ],
                'password' => [
                    'value' => $this->_configuration->getParameter('application/backendPassword'),
                ],
            ],
        ];
    }
}
