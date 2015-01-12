<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Service\V1\AuthorizationServiceInterface as AuthorizationService;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Plugin to allow customer users to access resources with self permission
 */
class CustomerAuthorization
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * Inject dependencies.
     *
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * Check if resource for which access is needed has self permissions defined in webapi config.
     *
     * @param \Magento\Framework\Authorization $subject
     * @param callable $proceed
     * @param string $resource
     * @param string $privilege
     *
     * @return bool true If resource permission is self, to allow
     * customer access without further checks in parent method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Framework\Authorization $subject,
        \Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource == AuthorizationService::PERMISSION_SELF
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
        ) {
            return true;
        } else {
            return $proceed($resource, $privilege);
        }
    }
}
