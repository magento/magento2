<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\Cart\Access;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Authorization\Model\UserContextInterface;

class ReadPlugin
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
     * @param \Magento\Checkout\Service\V1\Cart\ReadServiceInterface $subject
     * @param int $cartId
     *
     * @return void
     * @throws AuthorizationException if access denied
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetCart(
        \Magento\Checkout\Service\V1\Cart\ReadServiceInterface $subject,
        $cartId
    ) {
        if (!in_array($this->userContext->getUserType(), $this->allowedUserTypes)) {
            throw new AuthorizationException('Access denied');
        }
    }

    /**
     * Check whether access is allowed for cart list resource
     *
     * @param \Magento\Checkout\Service\V1\Cart\ReadServiceInterface $subject
     * @param SearchCriteria $searchCriteria
     *
     * @return void
     * @throws AuthorizationException if access denied
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetCartList(
        \Magento\Checkout\Service\V1\Cart\ReadServiceInterface $subject,
        SearchCriteria $searchCriteria
    ) {
        if (!in_array($this->userContext->getUserType(), $this->allowedUserTypes)) {
            throw new AuthorizationException('Access denied');
        }
    }
}
