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

/**
 * Address of registered customer
 */
class AddressBook extends \Mtf\Fixture\DataFixture
{
    /**
     * @var \Mtf\Fixture\DataFixture
     */
    protected $_addressFixture;

    /**
     * Nothing to initialize
     */
    protected function _initData()
    {
    }

    /**
     * Set address fixture
     *
     * @param \Mtf\Fixture\DataFixture $address
     */
    public function setAddress(\Mtf\Fixture\DataFixture $address)
    {
        $this->_addressFixture = $address;
    }

    /**
     * Switch current data set
     *
     * @param $name
     * @return bool
     */
    public function switchData($name)
    {
        $result = $this->_addressFixture->switchData($name);
        if (!$result) {
            return false;
        }
        $data = $this->_addressFixture->getData();
        $this->_data = array('fields' => array('address_id' => array(
            'value' => $data['fields']['firstname']['value'] . ' '
                . $data['fields']['lastname']['value'] . ', '
                . $data['fields']['street']['value'] . ', '
                . $data['fields']['city']['value'] . ', '
                . $data['fields']['region_id']['value'] . ' '
                . $data['fields']['postcode']['value'] . ', '
                . $data['fields']['country_id']['value'],
            'input' => 'select'
        )));

        return $result;
    }
}
