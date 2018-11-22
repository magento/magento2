<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\AddressDataProvider;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customers address update, used for GraphQL request processing.
 */
class UpdateCustomerAddress implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressDataProvider $addressDataProvider
     * @param Config $eavConfig
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        AddressRepositoryInterface $addressRepository,
        AddressDataProvider $addressDataProvider,
        DataObjectHelper $dataObjectHelper,
        Config $eavConfig
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->addressRepository = $addressRepository;
        $this->addressDataProvider = $addressDataProvider;
        $this->eavConfig = $eavConfig;
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
        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();

        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        return $this->addressDataProvider->processCustomerAddress(
            $this->processCustomerAddressUpdate($currentUserId, $args['id'], $args['input'])
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
        $attributes = $this->eavConfig->getEntityAttributes(
            AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS
        );
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
            $address = $this->addressRepository->getById($addressId);
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

        $this->dataObjectHelper->populateWithArray(
            $address,
            $addressInput,
            AddressInterface::class
        );
        return $this->addressRepository->save($address);
    }
}
