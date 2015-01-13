<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class Customer Group Fixture
 *
 */
class CustomerGroup extends DataFixture
{
    /**
     * Create customer group
     */
    public function persist()
    {
        $this->_data['fields']['id']['value'] = Factory::getApp()->magentoCustomerCreateCustomerGroup($this);
    }

    /**
     * Initialize fixture data
     */
    protected function _initData()
    {
        $this->_data = [
            'fields' => [
                'code' => [
                    'value' => 'Test group %isolation%',
                ],
                'tax_class' => [
                    'value' => 'Retail Customer',
                    'input_value' => 3,
                ],
            ],
        ];
        $this->_defaultConfig = [];

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCustomerCustomerGroup($this->_dataConfig, $this->_data);
    }

    /**
     * Get group name
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->getData('fields/code/value');
    }

    /**
     * Get group id
     *
     * @return string
     */
    public function getGroupId()
    {
        return $this->getData('fields/id/value');
    }
}
