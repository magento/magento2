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
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Resolver\Address\AddressDataProvider;
use Magento\CustomerGraphQl\Model\Resolver\Address\AddressConfigProvider;

/**
 * Customers address update, used for GraphQL request processing.
 */
class AddressUpdate implements ResolverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryInterface;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

    /**
     * @var AddressConfigProvider
     */
    public $addressConfigProvider;

    /**
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param AddressDataProvider $addressDataProvider
     * @param AddressConfigProvider $addressConfigProvider
     */
    public function __construct(
        AddressRepositoryInterface $addressRepositoryInterface,
        AddressInterfaceFactory $addressInterfaceFactory,
        AddressDataProvider $addressDataProvider,
        AddressConfigProvider $addressConfigProvider
    ) {
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->addressDataProvider = $addressDataProvider;
        $this->addressConfigProvider = $addressConfigProvider;
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
        return $this->addressDataProvider->processCustomerAddress(
            $this->processCustomerAddressUpdate($customerId, $args['id'], $args['input'])
        );
    }

    /**
     * Get update address attribute input errors
     *
     * @param array $addressInput
     * @return bool|string
     */
    private function getInputError(array $addressInput)
    {
        $attributes = $this->addressConfigProvider->getAddressAttributes();
        foreach ($attributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (isset($addressInput[$attributeName]) && empty($addressInput[$attributeName]))) {
                return $attributeName;
            }
        }
        return false;
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
        $errorInput = $this->getInputError($addressInput);
        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameter %1 is missing', [$errorInput])
            );
        }
        return $this->addressRepositoryInterface->save(
            $this->addressConfigProvider->fillAddress($address, $addressInput)
        );
    }
}
