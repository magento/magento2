<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Api\AuthorizationServiceInterface as AuthorizationService;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Plugin to allow guest users to access resources with anonymous permission
 * @since 2.0.0
 */
class GuestAuthorization
{
    /**
     * Check if resource for which access is needed has anonymous permissions defined in webapi config.
     *
     * @param \Magento\Framework\Authorization $subject
     * @param callable $proceed
     * @param string $resource
     * @param string $privilege
     * @return bool true If resource permission is anonymous,
     * to allow any user access without further checks in parent method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundIsAllowed(
        \Magento\Framework\Authorization $subject,
        \Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource == AuthorizationService::PERMISSION_ANONYMOUS) {
            return true;
        } else {
            return $proceed($resource, $privilege);
        }
    }
}
