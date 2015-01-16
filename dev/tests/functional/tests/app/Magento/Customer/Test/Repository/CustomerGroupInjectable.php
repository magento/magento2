<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CustomerGroupInjectable
 * CustomerGroup repository
 */
class CustomerGroupInjectable extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['General'] = [
            'customer_group_id' => '1',
            'customer_group_code' => 'General',
            'tax_class_id' => ['dataSet' => 'Retail Customer'],
        ];

        $this->_data['Retailer'] = [
            'customer_group_id' => '3',
            'customer_group_code' => 'Retailer',
            'tax_class_id' => ['dataSet' => 'Retail Customer'],
        ];

        $this->_data['Wholesale'] = [
            'customer_group_id' => '2',
            'customer_group_code' => 'Wholesale',
            'tax_class_id' => ['dataSet' => 'Retail Customer'],
        ];

        $this->_data['All Customer Groups'] = [
            'customer_group_id' => '0',
            'customer_group_code' => 'All Customer Groups',
        ];

        $this->_data['NOT LOGGED IN'] = [
            'customer_group_id' => '0',
            'customer_group_code' => 'NOT LOGGED IN',
            'tax_class_id' => ['dataSet' => 'Retail Customer'],
        ];
    }
}
