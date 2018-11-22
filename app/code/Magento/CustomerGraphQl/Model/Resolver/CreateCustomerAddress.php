<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\AddressDataProvider;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Customers address create, used for GraphQL request processing.
 */
class CreateCustomerAddress implements ResolverInterface
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
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

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
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param AddressDataProvider $addressDataProvider
     * @param Config $eavConfig
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressInterfaceFactory,
        AddressDataProvider $addressDataProvider,
        Config $eavConfig,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->addressRepository = $addressRepository;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
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
            $this->processCustomerAddressCreate($currentUserId, $args['input'])
        );
    }

    /**
     * Get new address attribute input errors
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
                && (!isset($addressInput[$attributeName]) || empty($addressInput[$attributeName]))) {
                return $attributeName;
            }
        }
        return false;
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
        $errorInput = $this->getInputError($addressInput);
        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameter %1 is missing', [$errorInput])
            );
        }
        /** @var AddressInterface $newAddress */
        $newAddress = $this->addressInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $newAddress,
            $addressInput,
            AddressInterface::class
        );
        $newAddress->setCustomerId($customerId);
        return $this->addressRepository->save($newAddress);
    }
}
