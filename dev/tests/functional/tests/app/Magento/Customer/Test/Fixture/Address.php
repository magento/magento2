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
use Mtf\Fixture\FixtureInterface;

/**
 * Class Address
 * Customer addresses
 *
 */
class Address extends DataFixture
{
    /**
     * @var FixtureInterface
     */
    protected $_customer;

    /**
     * Format customer address to one line
     *
     * @return string
     */
    public function getOneLineAddress()
    {
        $data = $this->getData();
        $address = isset($data['fields']['prefix']['value']) ? $data['fields']['prefix']['value'] . ' ' : ''
            . $data['fields']['firstname']['value'] . ' '
            . (isset($data['fields']['middlename']['value']) ? $data['fields']['middlename']['value'] . ' ' : '')
            . $data['fields']['lastname']['value'] . ', '
            . (isset($data['fields']['suffix']['value']) ? $data['fields']['suffix']['value'] . ' ' : '')
            . $data['fields']['street']['value'] . ', '
            . $data['fields']['city']['value'] . ', '
            . $data['fields']['region_id']['value'] . ' '
            . $data['fields']['postcode']['value'] . ', '
            . $data['fields']['country_id']['value'];

        return $address;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData('fields/city/value');
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->getData('fields/country/value');
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getData('fields/firstname');
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getData('fields/lastname');
    }

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->getData('fields/postcode/value');
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData('fields/region/value');
    }

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData('fields/telephone');
    }

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCustomerAddress($this->_dataConfig, $this->_data);

        //Default data set
        $this->switchData('address_US_1');
    }

    /**
     * Set customer
     *
     * @param FixtureInterface $customer
     */
    public function setCustomer(FixtureInterface $customer)
    {
        $this->_customer = $customer;
    }

    /**
     * Persists prepared data into application
     */
    public function persist()
    {
        Factory::getApp()->magentoCustomerCreateAddress($this);
    }

    /**
     * Get customer
     *
     * @return FixtureInterface
     */
    public function getCustomer()
    {
        return $this->_customer;
    }
}
