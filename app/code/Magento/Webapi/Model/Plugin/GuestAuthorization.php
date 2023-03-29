<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Api\AuthorizationServiceInterface as AuthorizationService;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Allow guest users to access resources with "anonymous" resource
 */
class GuestAuthorization
{
    /**
     * Check if resource for which access is needed has anonymous permissions defined in webapi config.
     *
     * @param \Magento\Framework\Authorization $subject
     * @param \Closure $proceed
     * @param string $resource
     * @param string $privilege
     * @return bool Is resource permission "anonymous", to allow any user access without further checks in parent method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Framework\Authorization $subject,
        \Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource === AuthorizationService::PERMISSION_ANONYMOUS) {
            return true;
        }

        return $proceed($resource, $privilege);
    }
}
