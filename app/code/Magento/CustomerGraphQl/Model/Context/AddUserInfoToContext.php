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
     * @param UserContextInterface $userContext
     */
    public function __construct(
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
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

        $contextParameters->addExtensionAttribute('is_customer', $this->isCustomer($currentUserId, $currentUserType));
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
