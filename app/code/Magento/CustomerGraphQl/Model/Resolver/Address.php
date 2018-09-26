<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Resolver\Address\AddressDataProvider;
use Magento\Eav\Model\Config;

/**
 * Customers Address, used for GraphQL request processing.
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
    private $customerRepositoryInterface;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryInterface;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param Config $eavConfig
     * @param AddressDataProvider $addressDataProvider
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        AddressRepositoryInterface $addressRepositoryInterface,
        AddressInterfaceFactory $addressInterfaceFactory,
        Config $eavConfig,
        AddressDataProvider $addressDataProvider,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->eavConfig = $eavConfig;
        $this->addressDataProvider = $addressDataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context */
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepositoryInterface->getById($context->getUserId());
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
     * @param array $addressInput
     * @return bool|string
     */
    private function getAddressInputError(array $addressInput)
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
     * @param string $attributeName
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
     * Add $addressInput array information to a $address object
     * @param AddressInterface $address
     * @param array $addressInput
     * @return AddressInterface
     */
    private function fillAddress(AddressInterface $address, array $addressInput) : AddressInterface
    {
        $this->dataObjectHelper->populateWithArray(
            $address,
            $addressInput,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        return $address;
    }

    /**
     * Process customer address create
     * @param CustomerInterface $customer
     * @param array $addressInput
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    private function processCustomerAddressCreate(CustomerInterface $customer, array $addressInput) : AddressInterface
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
    private function processCustomerAddressUpdate(CustomerInterface $customer, $addressId, array $addressInput)
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
    private function processCustomerAddressDelete(CustomerInterface $customer, $addressId)
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
