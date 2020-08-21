<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Closure;
use Magento\Customer\Model\Customer\AuthorizationComposite;
use Magento\Framework\Authorization;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Plugin to allow customer users to access resources with self permission
 */
class CustomerAuthorization
{
    /**
     * @var AuthorizationComposite
     */
    private $authorizationComposite;

    /**
     * Inject dependencies.
     * @param AuthorizationComposite $composite
     */
    public function __construct(
        AuthorizationComposite $composite
    ) {
        $this->authorizationComposite = $composite;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Authorization $subject
     * @param Closure $proceed
     * @param $resource
     * @param null $privilege
     * @return bool|mixed
     */
    public function aroundIsAllowed(
        Authorization $subject,
        Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($this->authorizationComposite->isAllowed($resource, $privilege)) {
            return true;
        }

        return $proceed($resource, $privilege);
    }
}
