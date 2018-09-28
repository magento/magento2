<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CustomerGraphQl\Model\Resolver\Address\AddressDataProvider;
use Magento\Eav\Model\Config;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customers Address, used for GraphQL request processing.
 */
class Address implements ResolverInterface
{
    /**
     * Mutation Address type
     */
    const MUTATION_ADDRESS_CREATE = 'customerAddressCreate';
    const MUTATION_ADDRESS_UPDATE = 'customerAddressUpdate';
    const MUTATION_ADDRESS_DELETE = 'customerAddressDelete';

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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var array
     */
    private $addressAttributes;

    /**
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param Config $eavConfig
     * @param AddressDataProvider $addressDataProvider
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        AddressRepositoryInterface $addressRepositoryInterface,
        AddressInterfaceFactory $addressInterfaceFactory,
        Config $eavConfig,
        AddressDataProvider $addressDataProvider,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->eavConfig = $eavConfig;
        $this->addressDataProvider = $addressDataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressAttributes = $this->eavConfig->getEntityAttributes(
            AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS
        );
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
                    [AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS]
                )
            );
        }
        $customerId = $context->getUserId();
        switch ($field->getName()) {
            case self::MUTATION_ADDRESS_CREATE:
                return $this->addressDataProvider->processCustomerAddress(
                    $this->processCustomerAddressCreate($customerId, $args['input'])
                );
            case self::MUTATION_ADDRESS_UPDATE:
                return $this->addressDataProvider->processCustomerAddress(
                    $this->processCustomerAddressUpdate($customerId, $args['id'], $args['input'])
                );
            case self::MUTATION_ADDRESS_DELETE:
                return $this->processCustomerAddressDelete($customerId, $args['id']);
            default:
                return [];
        }
    }

    /**
     * Get new address attribute input errors
     *
     * @param array $addressInput
     * @return bool|string
     */
    private function getNewAddressInputError(array $addressInput)
    {
        foreach ($this->addressAttributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (!isset($addressInput[$attributeName]) || empty($addressInput[$attributeName]))) {
                return $attributeName;
            }
        }
        return false;
    }

    /**
     * Get update address attribute input errors
     *
     * @param array $addressInput
     * @return bool|string
     */
    private function getUpdateAddressInputError(array $addressInput)
    {
        foreach ($this->addressAttributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (isset($addressInput[$attributeName]) && empty($addressInput[$attributeName]))) {
                return $attributeName;
            }
        }
        return false;
    }

    /**
     * Add $addressInput array information to a $address object
     *
     * @param AddressInterface $address
     * @param array $addressInput
     * @return AddressInterface
     */
    private function fillAddress(AddressInterface $address, array $addressInput) : AddressInterface
    {
        $this->dataObjectHelper->populateWithArray(
            $address,
            $addressInput,
            AddressInterface::class
        );
        return $address;
    }

    /**
     * Process customer address create
     *
     * @param int $customerId
     * @param array $addressInput
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    private function processCustomerAddressCreate($customerId, array $addressInput) : AddressInterface
    {
        $errorInput = $this->getNewAddressInputError($addressInput);
        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameter %1 is missing', [$errorInput])
            );
        }
        /** @var AddressInterface $newAddress */
        $newAddress = $this->fillAddress(
            $this->addressInterfaceFactory->create(),
            $addressInput
        );
        $newAddress->setCustomerId($customerId);
        return $this->addressRepositoryInterface->save($newAddress);
    }

    /**
     * Process customer address update
     *
     * @param int $customerId
     * @param int $addressId
     * @param array $addressInput
     * @return AddressInterface
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     */
    private function processCustomerAddressUpdate($customerId, $addressId, array $addressInput)
    {
        try {
            /** @var AddressInterface $address */
            $address = $this->addressRepositoryInterface->getById($addressId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Address id %1 does not exist.', [$addressId])
            );
        }
        if ($address->getCustomerId() != $customerId) {
            throw new GraphQlAuthorizationException(
                __('Current customer does not have permission to update address id %1', [$addressId])
            );
        }
        $errorInput = $this->getUpdateAddressInputError($addressInput);
        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameter %1 is missing', [$errorInput])
            );
        }
        return $this->addressRepositoryInterface->save(
            $this->fillAddress($address, $addressInput)
        );
    }

    /**
     * Process customer address delete
     *
     * @param int $customerId
     * @param int $addressId
     * @return bool
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    private function processCustomerAddressDelete($customerId, $addressId)
    {
        try {
            /** @var AddressInterface $address */
            $address = $this->addressRepositoryInterface->getById($addressId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Address id %1 does not exist.', [$addressId])
            );
        }
        if ($customerId != $address->getCustomerId()) {
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
