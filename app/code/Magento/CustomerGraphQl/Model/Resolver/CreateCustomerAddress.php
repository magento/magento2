<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\CustomerAddressCreateDataValidator;
use Magento\CustomerGraphQl\Model\Customer\Address\CustomerAddressDataProvider;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers address create, used for GraphQL request processing
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
     * @var CustomerAddressDataProvider
     */
    private $customerAddressDataProvider;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CustomerAddressCreateDataValidator
     */
    private $customerAddressCreateDataValidator;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param CustomerAddressDataProvider $customerAddressDataProvider
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerAddressCreateDataValidator $customerAddressCreateDataValidator
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressInterfaceFactory,
        CustomerAddressDataProvider $customerAddressDataProvider,
        DataObjectHelper $dataObjectHelper,
        CustomerAddressCreateDataValidator $customerAddressCreateDataValidator
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->addressRepository = $addressRepository;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->customerAddressDataProvider = $customerAddressDataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerAddressCreateDataValidator = $customerAddressCreateDataValidator;
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
        $this->customerAddressCreateDataValidator->validate($args['input']);

        $address = $this->createCustomerAddress((int)$currentUserId, $args['input']);
        return $this->customerAddressDataProvider->getAddressData($address);
    }

    /**
     * Create customer address
     *
     * @param int $customerId
     * @param array $addressData
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    private function createCustomerAddress(int $customerId, array $addressData) : AddressInterface
    {
        /** @var AddressInterface $address */
        $address = $this->addressInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray($address, $addressData, AddressInterface::class);
        $address->setCustomerId($customerId);

        try {
            $address = $this->addressRepository->save($address);
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        return $address;
    }
}
