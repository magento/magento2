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

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class Addresses
 * Customer addresses edit block
 *
 */
class Addresses extends Tab
{
    /**
     * "Add New Customer" button
     *
     * @var string
     */
    protected $addNewAddress = '#add_address_button';

    /**
     * Open customer address
     *
     * @var string
     */
    protected $customerAddress = '//*[@id="address_list"]/li[%d]/a';

    /**
     * Magento loader
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Fill customer addresses
     *
     * @param FixtureInterface|FixtureInterface[] $address
     * @return $this
     */
    public function fillAddresses($address)
    {
        $addresses = is_array($address) ? $address : [$address];
        foreach ($addresses as $address) {
            $this->addNewAddress();

            /* Fix switch between region_id and region */
            /** @var AddressInjectable $address */
            $countryId = $address->getCountryId();
            if ($countryId && $this->mapping['country_id']) {
                $this->_fill($this->dataMapping(['country_id' => $countryId]));
                $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
            }

            $this->fillFormTab($address->getData(), $this->_rootElement);
        }

        return $this;
    }

    /**
     * Update customer addresses
     *
     * @param FixtureInterface|FixtureInterface[] $address
     * @return $this
     * @throws \Exception
     */
    public function updateAddresses($address)
    {
        $addresses = is_array($address) ? $address : [1 => $address];
        foreach ($addresses as $addressNumber => $address) {
            /* Throw exception if isn't exist previous customer address. */
            if (1 < $addressNumber && !$this->isVisibleCustomerAddress($addressNumber - 1)) {
                throw new \Exception("Invalid argument: can't update customer address #{$addressNumber}");
            }

            if (!$this->isVisibleCustomerAddress($addressNumber)) {
                $this->addNewAddress();
            }
            $this->openCustomerAddress($addressNumber);

            /* Fix switch between region_id and region */
            /** @var AddressInjectable $address */
            $countryId = $address->getCountryId();
            if ($countryId && $this->mapping['country_id']) {
                $this->_fill($this->dataMapping(['country_id' => $countryId]));
                $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
            }
            $this->fillFormTab($address->getData(), $this->_rootElement);
        }

        return $this;
    }

    /**
     * Get data of Customer addresses
     *
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return array
     * @throws \Exception
     */
    public function getDataAddresses($address = null)
    {
        $data = [];
        $addresses = is_array($address) ? $address : [1 => $address];

        foreach ($addresses as $addressNumber => $address) {
            $isHasData = (null === $address) || $address->hasData();
            $isVisibleCustomerAddress = $this->isVisibleCustomerAddress($addressNumber);

            if ($isHasData && !$isVisibleCustomerAddress) {
                throw new \Exception("Invalid argument: can't get data from customer address #{$addressNumber}");
            }

            if (!$isHasData && !$isVisibleCustomerAddress) {
                $data[$addressNumber] = [];
            } else {
                $this->openCustomerAddress($addressNumber);
                $data[$addressNumber] = $this->getData($address, $this->_rootElement);
            }
        }

        return $data;
    }

    /**
     * Get data to fields on tab
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        /* Skip get data for standard method. Use getDataAddresses. */
        return [];
    }

    /**
     * Click "Add New Address" button
     */
    protected function addNewAddress()
    {
        $this->_rootElement->find($this->addNewAddress)->click();
    }

    /**
     * Open customer address
     *
     * @param int $addressNumber
     * @throws \Exception
     */
    protected function openCustomerAddress($addressNumber)
    {
        $addressTab = $this->_rootElement->find(
            sprintf($this->customerAddress, $addressNumber),
            Locator::SELECTOR_XPATH
        );

        if (!$addressTab->isVisible()) {
            throw new \Exception("Can't open customer address #{$addressNumber}");
        }
        $addressTab->click();
    }

    /**
     * Check is visible customer address
     *
     * @param int $addressNumber
     * @return bool
     */
    protected function isVisibleCustomerAddress($addressNumber)
    {
        $addressTab = $this->_rootElement->find(
            sprintf($this->customerAddress, $addressNumber),
            Locator::SELECTOR_XPATH
        );
        return $addressTab->isVisible();
    }
}
