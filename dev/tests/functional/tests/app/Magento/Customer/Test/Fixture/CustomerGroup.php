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
        $this->_data = array(
            'fields' => array(
                'code' => array(
                    'value' => 'Test group %isolation%',
                ),
                'tax_class' => array(
                    'value' => 'Retail Customer',
                    'input_value' => 3,
                ),
            ),
        );
        $this->_defaultConfig = array();

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
