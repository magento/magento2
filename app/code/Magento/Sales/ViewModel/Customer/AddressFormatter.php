<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Customer;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer address formatter
 */
class AddressFormatter implements ArgumentInterface
{
    /**
     * Customer form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $customerFormFactory;

    /**
     * Address format helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    private $addressFormatHelper;

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
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Helper\Address $addressFormatHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Backend\Model\Session\Quote $session
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Helper\Address $addressFormatHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Backend\Model\Session\Quote $session,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
    ) {
        $this->customerFormFactory = $customerFormFactory;
        $this->addressFormatHelper = $addressFormatHelper;
        $this->directoryHelper = $directoryHelper;
        $this->session = $session;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Return customer address array as JSON
     *
     * @param array $addressArray
     *
     * @return string
     */
    public function getAddressesJson(array $addressArray): string
    {
        $data = $this->getEmptyAddressForm();
        foreach ($addressArray as $addressId => $address) {
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
        $formatTypeRenderer = $this->addressFormatHelper->getFormatTypeRenderer('oneline');
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
