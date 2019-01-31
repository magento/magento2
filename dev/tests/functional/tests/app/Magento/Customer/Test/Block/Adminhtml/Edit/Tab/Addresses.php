<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\ModuleResolver\SequenceSorterInterface;

/**
 * Customer addresses edit block.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Addresses extends Tab
{
    /**
     * "Add New Customer" button.
     *
     * @var string
     */
    protected $addNewAddress = '.add-new-address-button';

    /**
     * Selector for address block.
     *
     * @var string
     */
    protected $addressSelector = "//li[address[contains(.,'%s')]]";

    protected $countriesSelector = "//*/select[@name='address[new_%d][country_id]']/option";

    /**
     * Delete Address button.
     *
     * @var string
     */
    protected $deleteAddress = '.action-delete';

    /**
     * Open customer address.
     *
     * @var string
     */
    protected $customerAddress = '//*[contains(@class, "address-list-item")][%d]';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Customer address modal window.
     *
     * @var string
     */
    private $customerAddressModalForm = '.customer_form_areas_address_address_customer_address_update_modal';

    /**
     * Customer addresses list grid.
     *
     * @var string
     */
    private $customerAddressesGrid = '.customer_form_areas_address_address_customer_address_listing';

    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @constructor
     * @param SimpleElement $element
     * @param BlockFactory $blockFactory
     * @param Mapper $mapper
     * @param BrowserInterface $browser
     * @param SequenceSorterInterface $sequenceSorter
     * @param ObjectManager $objectManager
     * @param array $config [optional]
     */
    public function __construct(
        SimpleElement $element,
        BlockFactory $blockFactory,
        Mapper $mapper,
        BrowserInterface $browser,
        SequenceSorterInterface $sequenceSorter,
        ObjectManager $objectManager,
        array $config = []
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($element, $blockFactory, $mapper, $browser, $sequenceSorter, $config);
    }

    /**
     * Fill customer addresses.
     *
     * @param FixtureInterface|FixtureInterface[] $address
     * @return $this
     */
    public function fillAddresses($address)
    {
        $addresses = is_array($address) ? $address : [$address];
        $customerAddressForm = $this->getCustomerAddressModalForm();
        foreach ($addresses as $address) {
            $this->addNewAddress();
            $customerAddressForm->fillAddressData($address);
            $customerAddressForm->saveAddress();
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
            } else {
                $this->openCustomerAddress($addressNumber);
            }

            $defaultAddress = ['default_billing' => 'No', 'default_shipping' => 'No'];
            $addressData = $address->getData();
            foreach ($defaultAddress as $key => $value) {
                if (isset($addressData[$key])) {
                    $defaultAddress[$key] = $value;
                }
            }
            $customerAddressForm = $this->getCustomerAddressModalForm();
            $customerAddressForm->setFieldsData($this->dataMapping($defaultAddress));
            $customerAddressForm->setFieldsData(array_diff($addressData, $defaultAddress));
            $customerAddressForm->saveAddress();
        }

        return $this;
    }

    /**
     * Get data from Customer addresses.
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
            $hasData = (null !== $address) && $address->hasData();
            $customerAddressesGrid = $this->getCustomerAddressesGrid();
            if ($hasData) {
                $customerAddressesGrid->search($address->getData());
            }
            $isVisibleCustomerAddress = $this->isVisibleCustomerAddress($addressNumber);

            if ($hasData && !$isVisibleCustomerAddress) {
                throw new \Exception("Invalid argument: can't get data from customer address #{$addressNumber}");
            }

            if (!$hasData && !$isVisibleCustomerAddress) {
                $data[$addressNumber] = [];
            } else {
                $customerAddressesGrid->openFirstRow();
                $data[$addressNumber] = $this->getCustomerAddressModalForm()
                    ->getData($address, $this->browser->find($this->customerAddressModalForm));
                $this->getCustomerAddressModalForm()->clickCancelButton();
            }
        }

        return $data;
    }

    /**
     * Get data from Customer addresses.
     *
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return array|null
     * @throws \Exception
     */
    public function getAddressFromFirstRow($address = null)
    {
        $customerAddressesGrid = $this->getCustomerAddressesGrid();
        $customerAddressesGrid->resetFilter();
        $customerAddressesGrid->openFirstRow();
        if ($this->getCustomerAddressModalForm()->isVisible()) {
            $address = $this->getCustomerAddressModalForm()
                ->getData($address, $this->browser->find($this->customerAddressModalForm));
        }

        return $address;
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
    public function getFieldsData($fields = null, SimpleElement $element = null)
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
        $this->waitForElementVisible($this->customerAddressModalForm);
    }

    /**
     * Open customer address.
     *
     * @param int $addressNumber
     * @throws \Exception
     */
    protected function openCustomerAddress($addressNumber)
    {
        $customerAddressesGrid = $this->getCustomerAddressesGrid();
        if (!$customerAddressesGrid->getFirstRow()->isVisible()) {
            throw new \Exception("Can't open customer address #{$addressNumber}");
        }
        $customerAddressesGrid->openFirstRow();
    }

    /**
     * Check is visible customer address.
     *
     * @return bool
     */
    protected function isVisibleCustomerAddress()
    {
        $customerAddressesGrid = $this->getCustomerAddressesGrid();
        $customerAddressesGrid->isFirstRowVisible();

        return $customerAddressesGrid->isFirstRowVisible();
    }

    /**
     * Retrieve list of all countries
     * @param int $addressNumber
     * @return array
     */
    public function getCountriesList($addressNumber)
    {
        $this->openCustomerAddress($addressNumber);
        /** @var SimpleElement $element */
        $options = $this->_rootElement->getElements(
            sprintf($this->countriesSelector, $addressNumber - 1),
            Locator::SELECTOR_XPATH
        );
        $data = [];
        /** @var SimpleElement $option */
        foreach ($options as $option) {
            if ($option->isVisible()) {
                $value = $option->getValue();

                if ($value != "") {
                    $data[] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Click delete customer address button.
     *
     * @param Address $addressToDelete
     * @return $this
     */
    public function deleteCustomerAddress(Address $addressToDelete)
    {
        $customerAddressesGrid = $this->getCustomerAddressesGrid();
        $customerAddressesGrid->deleteCustomerAddress($addressToDelete->getData());

        return $this;
    }

    /**
     * Get new/update customer address modal form.
     *
     * @return \Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses\AddressForm
     */
    public function getCustomerAddressModalForm()
    {
        return $this->blockFactory->create(
            \Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses\AddressForm::class,
            ['element' => $this->browser->find($this->customerAddressModalForm)]
        );
    }

    /**
     * Get customer addresses grid.
     *
     * @return \Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses\AddressesGrid
     */
    public function getCustomerAddressesGrid()
    {
        return $this->blockFactory->create(
            \Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses\AddressesGrid::class,
            ['element' => $this->browser->find($this->customerAddressesGrid)]
        );
    }

    /**
     * Wait for addresses grid rendering
     */
    public function waitForAddressesGrid()
    {
        $this->waitForElementVisible($this->customerAddressesGrid);
    }
}
