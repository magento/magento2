<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
