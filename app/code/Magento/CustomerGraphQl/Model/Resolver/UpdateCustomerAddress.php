<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\CustomerAddressDataProvider;
use Magento\CustomerGraphQl\Model\Customer\Address\CustomerAddressUpdateDataValidator;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddressForUser;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers address update, used for GraphQL request processing
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
     * @var CustomerAddressDataProvider
     */
    private $customerAddressDataProvider;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CustomerAddressUpdateDataValidator
     */
    private $customerAddressUpdateDataValidator;

    /**
     * @var GetCustomerAddressForUser
     */
    private $getCustomerAddressForUser;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerAddressDataProvider $customerAddressDataProvider
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerAddressUpdateDataValidator $customerAddressUpdateDataValidator
     * @param GetCustomerAddressForUser $getCustomerAddressForUser
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        AddressRepositoryInterface $addressRepository,
        CustomerAddressDataProvider $customerAddressDataProvider,
        DataObjectHelper $dataObjectHelper,
        CustomerAddressUpdateDataValidator $customerAddressUpdateDataValidator,
        GetCustomerAddressForUser $getCustomerAddressForUser
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->addressRepository = $addressRepository;
        $this->customerAddressDataProvider = $customerAddressDataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerAddressUpdateDataValidator = $customerAddressUpdateDataValidator;
        $this->getCustomerAddressForUser = $getCustomerAddressForUser;
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
        $this->customerAddressUpdateDataValidator->validate($args['input']);

        $address = $this->updateCustomerAddress((int)$currentUserId, (int)$args['id'], $args['input']);
        return $this->customerAddressDataProvider->getAddressData($address);
    }

    /**
     * Update customer address
     *
     * @param int $customerId
     * @param int $addressId
     * @param array $addressData
     * @return AddressInterface
     */
    private function updateCustomerAddress(int $customerId, int $addressId, array $addressData)
    {
        $address = $this->getCustomerAddressForUser->execute($addressId, $customerId);
        $this->dataObjectHelper->populateWithArray($address, $addressData, AddressInterface::class);
        if (isset($addressData['region']['region_id'])) {
            $address->setRegionId($address->getRegion()->getRegionId());
        }

        return $this->addressRepository->save($address);
    }
}
