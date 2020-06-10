<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\ContextParametersProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;

/**
 * @inheritdoc
 */
class AddUserInfoToContext implements ContextParametersProcessorInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $currentUserId = $this->userContext->getUserId();
        if (null !== $currentUserId) {
            $currentUserId = (int)$currentUserId;
        }
        $contextParameters->setUserId($currentUserId);

        $currentUserType = $this->userContext->getUserType();
        if (null !== $currentUserType) {
            $currentUserType = (int)$currentUserType;
        }
        $contextParameters->setUserType($currentUserType);

        if ($isCustomer = $this->isCustomer($currentUserId, $currentUserType)) {

            $contextParameters->addExtensionAttribute('is_customer', $isCustomer);

            try {
                $customerGroupId = $this->customerRepository->getById($currentUserId)->getGroupId();
            } catch (\Exception $e) {
                $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
            }

            $contextParameters->addExtensionAttribute('customer_group_id', $customerGroupId);
        }

        return $contextParameters;
    }

    /**
     * Checking if current user is logged
     *
     * @param int|null $customerId
     * @param int|null $customerType
     * @return bool
     */
    private function isCustomer(?int $customerId, ?int $customerType): bool
    {
        return !empty($customerId) && !empty($customerType) && $customerType !== UserContextInterface::USER_TYPE_GUEST;
    }
}
