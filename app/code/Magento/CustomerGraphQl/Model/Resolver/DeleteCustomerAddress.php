<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customers address delete, used for GraphQL request processing.
 */
class DeleteCustomerAddress implements ResolverInterface
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
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->addressRepository = $addressRepository;
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

        return $this->processCustomerAddressDelete($currentUserId, $args['id']);
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
            $address = $this->addressRepository->getById($addressId);
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
        return $this->addressRepository->delete($address);
    }
}
