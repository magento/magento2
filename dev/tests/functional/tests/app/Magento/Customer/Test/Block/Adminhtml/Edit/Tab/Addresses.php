<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Customer addresses edit block.
 */
class Addresses extends Tab
{
    /**
     * "Add New Customer" button.
     *
     * @var string
     */
    protected $addNewAddress = '.address-list-actions .add';

    /**
     * Open customer address.
     *
     * @var string
     */
    protected $customerAddress = '//*[contains(@class, "address-list-item")][%d]';

    /**
     * Active address tab.
     *
     * @var string
     */
    protected $addressTab = '.address-item-edit[data-bind="visible: element.active"]:not([style="display: none;"])';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Fill customer addresses.
     *
     * @param FixtureInterface|FixtureInterface[] $address
     * @return $this
     */
    public function fillAddresses($address)
    {
        $addresses = is_array($address) ? $address : [$address];
        foreach ($addresses as $address) {
            $this->addNewAddress();
            $this->fillFormTab($address->getData(), $this->_rootElement);
        }

        return $this;
    }

    /**
     * Update customer addresses.
     *
     * @param FixtureInterface|FixtureInterface[] $address
     * @return $this
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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

            $defaultAddress = ['default_billing' => 'No', 'default_shipping' => 'No'];
            $addressData = $address->getData();
            foreach ($defaultAddress as $key => $value) {
                if (isset($addressData[$key])) {
                    $defaultAddress[$key] = $value;
                }
            }
            $this->_fill($this->dataMapping($defaultAddress));

            $this->fillFormTab(array_diff($addressData, $defaultAddress), $this->_rootElement);
        }

        return $this;
    }

    /**
     * Get data of Customer addresses.
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
     * Get data to fields on tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        /* Skip get data for standard method. Use getDataAddresses. */
        return [];
    }

    /**
     * Click "Add New Address" button.
     */
    protected function addNewAddress()
    {
        $this->_rootElement->find($this->addNewAddress)->click();
        $this->waitForElementVisible($this->addressTab);
    }

    /**
     * Open customer address.
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
     * Check is visible customer address.
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
