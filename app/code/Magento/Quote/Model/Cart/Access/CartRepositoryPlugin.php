<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Cart\Access;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Authorization\Model\UserContextInterface;

class CartRepositoryPlugin
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
     * Check whether access is allowed for cart resource
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param int $cartId
     *
     * @return void
     * @throws AuthorizationException if access denied
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGet(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        $cartId
    ) {
        if (!in_array($this->userContext->getUserType(), $this->allowedUserTypes)) {
            throw new AuthorizationException(__('Access denied'));
        }
    }

    /**
     * Check whether access is allowed for cart list resource
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param SearchCriteria $searchCriteria
     *
     * @return void
     * @throws AuthorizationException if access denied
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetList(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        SearchCriteria $searchCriteria
    ) {
        if (!in_array($this->userContext->getUserType(), $this->allowedUserTypes)) {
            throw new AuthorizationException(__('Access denied'));
        }
    }
}
