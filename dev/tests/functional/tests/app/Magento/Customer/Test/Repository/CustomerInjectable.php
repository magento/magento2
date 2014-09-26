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
 * Class CustomerInjectable
 * Customer repository
 */
class CustomerInjectable extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];

        $this->_data['johndoe'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'dob' => '01/01/1990',
            'gender' => 'Male',
            'group_id' => ['dataSet' => 'General'],
        ];

        $this->_data['johndoe_retailer'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'group_id' => ['dataSet' => 'Retailer'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'dob' => '01/01/1990',
            'gender' => 'Male',
        ];

        $this->_data['johndoe_with_balance'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'dob' => '01/01/1990',
            'gender' => 'Male',
            'amount_delta' => 501
        ];

        $this->_data['defaultBackend'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
        ];

        $this->_data['johndoe_with_addresses'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'address' => ['presets' => 'US_address']
        ];

        $this->_data['customer_US'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];

        $this->_data['customer_UK'] = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'JaneDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];

        $this->_data['johndoe_unique'] = [
            'firstname' => 'John',
            'lastname' => 'Doe%isolation%',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];
    }
}
