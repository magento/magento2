<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Customer;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer address
 */
class Address extends DataObject implements ArgumentInterface
{
    /**
     * Customer addresses collection
     *
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    private $addressCollection;

    /**
     * Customer address array
     *
     * @var array
     */
    private $addressArray;

    /**
     * Customer form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $customerFormFactory;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    private $addressHelper;

    /**
     * Directory helper
     *
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $session;

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonEncoder;

    /**
     * Customer address
     *
     * @param \Magento\Customer\Model\ResourceModel\Address\Collection $addressCollection
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Backend\Model\Session\Quote $session
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Address\Collection $addressCollection,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Backend\Model\Session\Quote $session,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
    ) {
        $this->addressCollection = $addressCollection;
        $this->customerFormFactory = $customerFormFactory;
        $this->addressHelper = $addressHelper;
        $this->directoryHelper = $directoryHelper;
        $this->session = $session;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct();
    }

    /**
     * Retrieve customer address array.
     *
     * @param int $customerId
     *
     * @return array
     */
    public function getAddresses(int $customerId): array
    {
        if ($customerId) {
            if ($this->addressArray === null) {
                $addressCollection = $this->addressCollection->setCustomerFilter([$customerId]);
                $this->addressArray = $addressCollection->toArray();
            }
            return $this->addressArray;
        }
        return [];
    }

    /**
     * Return customer address array as JSON
     *
     * @param int $customerId
     *
     * @return string
     */
    public function getAddressesJson(int $customerId): string
    {
        $data = $this->getEmptyAddressForm();
        foreach ($this->getAddresses($customerId) as $addressId => $address) {
            $addressForm = $this->customerFormFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                $address
            );
            $data[$addressId] = $addressForm->outputData(
                \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
            );
        }

        return $this->jsonEncoder->serialize($data);
    }

    /**
     * Represent customer address in 'online' format.
     *
     * @param array $address
     * @return string
     */
    public function getAddressAsString(array $address): string
    {
        $formatTypeRenderer = $this->addressHelper->getFormatTypeRenderer('oneline');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray($address);
        }

        return $result;
    }

    /**
     * Return empty address address form
     *
     * @return array
     */
    private function getEmptyAddressForm(): array
    {
        $defaultCountryId = $this->directoryHelper->getDefaultCountry($this->session->getStore());
        $emptyAddressForm = $this->customerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            [\Magento\Customer\Api\Data\AddressInterface::COUNTRY_ID => $defaultCountryId]
        );

        return [0 => $emptyAddressForm->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON)];
    }
}
