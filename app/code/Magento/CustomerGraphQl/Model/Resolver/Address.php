<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressExtensionInterfaceFactory;
use Magento\Framework\Api\AttributeInterfaceFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Resolver\Address\AddressDataProvider;
use Magento\Eav\Model\Config;

/**
 * Customers Address Update
 */
class Address implements ResolverInterface
{
    /**
     * Customer address attributes
     * @var array
     */
    const ADDRESS_ATTRIBUTES = [
        AddressInterface::REGION,
        AddressInterface::REGION_ID,
        AddressInterface::COUNTRY_ID,
        AddressInterface::STREET,
        AddressInterface::COMPANY,
        AddressInterface::TELEPHONE,
        AddressInterface::FAX,
        AddressInterface::POSTCODE,
        AddressInterface::CITY,
        AddressInterface::FIRSTNAME,
        AddressInterface::LASTNAME,
        AddressInterface::MIDDLENAME,
        AddressInterface::PREFIX,
        AddressInterface::SUFFIX,
        AddressInterface::VAT_ID
    ];

    /**
     * Input data key
     */
    const CUSTOM_ATTRIBUTE_KEY = 'custom_attributes';
    const EXTENSION_ATTRIBUTE_KEY = 'extension_attributes';

    /**
     * Mutation Address type
     */
    const MUTATION_ADDRESS_CREATE = 'customerAddressCreate';
    const MUTATION_ADDRESS_UPDATE = 'customerAddressUpdate';
    const MUTATION_ADDRESS_DELETE = 'customerAddressDelete';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryInterface;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionInterfaceFactory;

    /**
     * @var AttributeInterfaceFactory
     */
    private $attributeInterfaceFactory;

    /**
     * @var AddressExtensionInterfaceFactory
     */
    private $addressExtensionInterfaceFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param RegionInterfaceFactory $regionInterfaceFactory
     * @param AttributeInterfaceFactory $attributeInterfaceFactory
     * @param AddressExtensionInterfaceFactory $addressExtensionInterfaceFactory
     * @param Config $eavConfig
     * @param AddressDataProvider $addressDataProvider
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepositoryInterface,
        AddressInterfaceFactory $addressInterfaceFactory,
        RegionInterfaceFactory $regionInterfaceFactory,
        AttributeInterfaceFactory $attributeInterfaceFactory,
        AddressExtensionInterfaceFactory $addressExtensionInterfaceFactory,
        Config $eavConfig,
        AddressDataProvider $addressDataProvider
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->attributeInterfaceFactory = $attributeInterfaceFactory;
        $this->addressExtensionInterfaceFactory = $addressExtensionInterfaceFactory;
        $this->eavConfig = $eavConfig;
        $this->addressDataProvider = $addressDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById($context->getUserId());
        switch ($field->getName()) {
            case self::MUTATION_ADDRESS_CREATE:
                return $this->addressDataProvider->processCustomerAddress(
                    $this->processCustomerAddressCreate($customer, $args['input'])
                );
            case self::MUTATION_ADDRESS_UPDATE:
                return $this->addressDataProvider->processCustomerAddress(
                    $this->processCustomerAddressUpdate($customer, $args['id'], $args['input'])
                );
            case self::MUTATION_ADDRESS_DELETE:
                return $this->processCustomerAddressDelete($customer, $args['id']);
            default:
                return [];
        }
    }

    /**
     * Get input address attribute errors
     * @param $addressInput
     * @return bool|string
     */
    private function getAddressInputError($addressInput)
    {
        foreach (self::ADDRESS_ATTRIBUTES as $attribute) {
            if ($this->isAttributeRequired($attribute) && !isset($addressInput[$attribute])) {
                return $attribute;
            }
        }
        return false;
    }

    /**
     * Check if attribute is set as required
     * @param $attributeName
     * @return bool
     */
    private function isAttributeRequired($attributeName)
    {
        return $this->eavConfig->getAttribute(
            AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS,
            $attributeName
        )->getIsRequired();
    }

    /**
     * @param AddressInterface $address
     * @param array $addressInput
     * @return AddressInterface
     */
    private function fillAddress($address, $addressInput)
    {
        if (isset($addressInput[AddressInterface::REGION])) {
            /** @var \Magento\Customer\Api\Data\RegionInterface $newRegion */
            $newRegion = $this->regionInterfaceFactory->create($addressInput[AddressInterface::REGION]);
            $address->setRegion($newRegion);
        }
        if (isset($addressInput[AddressInterface::REGION_ID])) {
            $address->setRegionId($addressInput[AddressInterface::REGION_ID]);
        }
        if (isset($addressInput[AddressInterface::COUNTRY_ID])) {
            $address->setCountryId($addressInput[AddressInterface::COUNTRY_ID]);
        }
        if (isset($addressInput[AddressInterface::STREET])) {
            $address->setStreet($addressInput[AddressInterface::STREET]);
        }
        if (isset($addressInput[AddressInterface::COMPANY])) {
            $address->setCompany($addressInput[AddressInterface::COMPANY]);
        }
        if (isset($addressInput[AddressInterface::TELEPHONE])) {
            $address->setTelephone($addressInput[AddressInterface::TELEPHONE]);
        }
        if (isset($addressInput[AddressInterface::FAX])) {
            $address->setFax($addressInput[AddressInterface::FAX]);
        }
        if (isset($addressInput[AddressInterface::POSTCODE])) {
            $address->setPostcode($addressInput[AddressInterface::POSTCODE]);
        }
        if (isset($addressInput[AddressInterface::CITY])) {
            $address->setCity($addressInput[AddressInterface::CITY]);
        }
        if (isset($addressInput[AddressInterface::FIRSTNAME])) {
            $address->setFirstname($addressInput[AddressInterface::FIRSTNAME]);
        }
        if (isset($addressInput[AddressInterface::LASTNAME])) {
            $address->setLastname($addressInput[AddressInterface::LASTNAME]);
        }
        if (isset($addressInput[AddressInterface::MIDDLENAME])) {
            $address->setMiddlename($addressInput[AddressInterface::MIDDLENAME]);
        }
        if (isset($addressInput[AddressInterface::PREFIX])) {
            $address->setPrefix($addressInput[AddressInterface::PREFIX]);
        }
        if (isset($addressInput[AddressInterface::SUFFIX])) {
            $address->setSuffix($addressInput[AddressInterface::SUFFIX]);
        }
        if (isset($addressInput[AddressInterface::VAT_ID])) {
            $address->setVatId($addressInput[AddressInterface::VAT_ID]);
        }
        if (isset($addressInput[AddressInterface::DEFAULT_BILLING])) {
            $address->setIsDefaultBilling($addressInput[AddressInterface::DEFAULT_BILLING]);
        }
        if (isset($addressInput[AddressInterface::DEFAULT_SHIPPING])) {
            $address->setIsDefaultShipping($addressInput[AddressInterface::DEFAULT_SHIPPING]);
        }
        if (isset($addressInput[AddressInterface::DEFAULT_SHIPPING])) {
            $address->setIsDefaultShipping($addressInput[AddressInterface::DEFAULT_SHIPPING]);
        }
        if (isset($addressInput[self::CUSTOM_ATTRIBUTE_KEY])) {
            foreach ($addressInput[self::CUSTOM_ATTRIBUTE_KEY] as $attribute) {
                $address->setCustomAttribute($attribute['attribute_code'], $attribute['value']);
            }
        }
        if (isset($addressInput[self::EXTENSION_ATTRIBUTE_KEY])) {
            $extensionAttributes = $address->getExtensionAttributes();
            if (!$extensionAttributes) {
                /** @var \Magento\Customer\Api\Data\AddressExtensionInterface $newExtensionAttribute */
                $extensionAttributes = $this->addressExtensionInterfaceFactory->create();
            }
            foreach ($addressInput[self::EXTENSION_ATTRIBUTE_KEY] as $attribute) {
                $extensionAttributes->setData($attribute['attribute_code'], $attribute['value']);
            }
            $address->setExtensionAttributes($extensionAttributes);
        }
        return $address;
    }

    /**
     * Process customer address create
     * @param CustomerInterface $customer
     * @param array $addressInput
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    private function processCustomerAddressCreate($customer, $addressInput)
    {
        $errorInput = $this->getAddressInputError($addressInput);
        if ($errorInput) {
            throw new GraphQlInputException(__('Required parameter %1 is missing', [$errorInput]));
        }
        /** @var AddressInterface $newAddress */
        $newAddress = $this->fillAddress(
            $this->addressInterfaceFactory->create(),
            $addressInput
        );
        $newAddress->setCustomerId($customer->getId());
        return $this->addressRepositoryInterface->save($newAddress);
    }

    /**
     * Process customer address update
     * @param CustomerInterface $customer
     * @param int $addressId
     * @param array $addressInput
     * @return AddressInterface
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    private function processCustomerAddressUpdate($customer, $addressId, $addressInput)
    {
        try {
            /** @var AddressInterface $address */
            $address = $this->addressRepositoryInterface->getById($addressId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Address id %1 does not exist.', [$addressId])
            );
        }
        if ($address->getCustomerId() != $customer->getId()) {
            throw new GraphQlAuthorizationException(
                __('Current customer does not have permission to update address id %1', [$addressId])
            );
        }
        return $this->addressRepositoryInterface->save(
            $this->fillAddress($address, $addressInput)
        );
    }

    /**
     * Process customer address delete
     * @param CustomerInterface $customer
     * @param int $addressId
     * @return bool
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    private function processCustomerAddressDelete($customer, $addressId)
    {
        try {
            /** @var AddressInterface $address */
            $address = $this->addressRepositoryInterface->getById($addressId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Address id %1 does not exist.', [$addressId])
            );
        }
        if ($address->getCustomerId() != $customer->getId()) {
            throw new GraphQlAuthorizationException(
                __('Current customer does not have permission to delete address id %1', [$addressId])
            );
        }
        if ($address->isDefaultBilling()) {
            throw new GraphQlAuthorizationException(
                __('Customer Address %1 is set as default billing address and can not be deleted', [$addressId])
            );
        }
        if ($address->isDefaultShipping()) {
            throw new GraphQlAuthorizationException(
                __('Customer Address %1 is set as default shipping address and can not be deleted', [$addressId])
            );
        }
        return $this->addressRepositoryInterface->delete($address);
    }
}