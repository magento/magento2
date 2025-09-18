<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Factory as ValidatorFactory;
use Magento\Quote\Api\Data\AddressInterface;

class GuestShippingInformationManagement implements \Magento\Checkout\Api\GuestShippingInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement
     * @param ValidatorFactory $validatorFactory
     * @param AddressFactory $addressFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        ValidatorFactory $validatorFactory,
        AddressFactory $addressFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->validatorFactory = $validatorFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws InputException
     */
    public function saveAddressInformation(
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();
        if ($shippingAddress) {
            $this->validateAddressAttributes($shippingAddress, 'shipping');
        }

        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingInformationManagement->saveAddressInformation(
            (int) $quoteIdMask->getQuoteId(),
            $addressInformation
        );
    }

    /**
     * Validate address attributes using customer_address validator with custom attributes
     *
     * @param AddressInterface $address
     * @param string $addressType
     * @return void
     * @throws InputException
     */
    private function validateAddressAttributes(AddressInterface $address, string $addressType): void
    {
        try {
            $customerAddress = $this->createCustomerAddressFromQuoteAddress($address, $addressType);
            $extensionAttributes = $address->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributesData = $extensionAttributes->__toArray();
                foreach ($extensionAttributesData as $attributeCode => $value) {
                    if ($value !== null && $value !== '') {
                        $customerAddress->setData($attributeCode, $value);
                    }
                }
            }
            $customerAddress->setSkipRequiredValidation(true);
            $validator = $this->validatorFactory->createValidator('customer_address', 'save');
            if (!$validator->isValid($customerAddress)) {
                $this->throwValidationException($validator->getMessages(), $addressType);
            }
        } catch (LocalizedException $e) {
            throw new InputException(__($e->getMessage()));
        }
    }

    /**
     * Create customer address object from quote address
     *
     * @param AddressInterface $address
     * @param string $addressType
     * @return \Magento\Customer\Model\Address
     */
    private function createCustomerAddressFromQuoteAddress(
        AddressInterface $address,
        string $addressType
    ): \Magento\Customer\Model\Address {
        $customerAddress = $this->addressFactory->create();
        $customerAddress->setData([
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'region_id' => $address->getRegionId(),
            'region_code' => $address->getRegionCode(),
            'postcode' => $address->getPostcode(),
            'country_id' => $address->getCountryId(),
            'telephone' => $address->getTelephone(),
            'company' => $address->getCompany(),
            'email' => $address->getEmail(),
            'address_type' => $addressType
        ]);

        return $customerAddress;
    }

    /**
     * Process validator messages and throw validation exception
     *
     * @param array $messages
     * @param string $addressType
     * @return void
     * @throws InputException
     */
    private function throwValidationException(array $messages, string $addressType): void
    {
        $errorMessages = [];
        foreach ($messages as $message) {
            if (is_array($message)) {
                foreach ($message as $msg) {
                    $errorMessages[] = $msg;
                }
            } else {
                $errorMessages[] = $message;
            }
        }
        throw new InputException(
            __(
                'The %1 address contains invalid data: %2',
                $addressType,
                implode(', ', $errorMessages)
            )
        );
    }
}
