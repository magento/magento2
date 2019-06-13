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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param UserContextInterface $userContext
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        UserContextInterface $userContext,
        GetCustomer $getCustomer
    ) {
        $this->userContext = $userContext;
        $this->getCustomer = $getCustomer;
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

        $currentUserType = $this->userContext->getUserType();
        if (null !== $currentUserType) {
            $currentUserType = (int)$currentUserType;
        }

        if (false === $this->isUserGuest($currentUserId, $currentUserType)) {
            $customer = $this->getCustomer->execute($currentUserId);
            $contextParameters->addExtensionAttribute('customer', $customer);
        }

        $contextParameters->setUserId($currentUserId);
        $contextParameters->setUserType($currentUserType);
        return $contextParameters;
    }

    /**
     * Checking if current customer is guest
     *
     * @param int|null $customerId
     * @param int|null $customerType
     * @return bool
     */
    private function isUserGuest(?int $customerId, ?int $customerType): bool
    {
        if (null === $customerId || null === $customerType) {
            return true;
        }
        return 0 === (int)$customerId || (int)$customerType === UserContextInterface::USER_TYPE_GUEST;
    }
}
