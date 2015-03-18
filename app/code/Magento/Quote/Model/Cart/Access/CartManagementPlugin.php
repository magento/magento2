<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Cart\Access;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Authorization\Model\UserContextInterface;

class CartManagementPlugin
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var int[]
     */
    protected $allowedUserTypes = [
        UserContextInterface::USER_TYPE_ADMIN,
        UserContextInterface::USER_TYPE_INTEGRATION,
    ];

    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * Check whether access is allowed for create cart resource
     *
     * @param \Magento\Quote\Api\CartManagementInterface $subject
     * @param int $cartId
     * @param int $customerId
     * @param int $storeId
     *
     * @return void
     * @throws AuthorizationException if access denied
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAssignCustomer(
        \Magento\Quote\Api\CartManagementInterface $subject,
        $cartId,
        $customerId,
        $storeId
    ) {
        if (!in_array($this->userContext->getUserType(), $this->allowedUserTypes)) {
            throw new AuthorizationException(__('Access denied'));
        }
    }
}
