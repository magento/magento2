<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Plugin\Model;

use Magento\Integration\Model\AdminTokenService;

/**
 * Plugin to delete admin tokens when admin becomes inactive
 * @since 2.2.0
 */
class AdminUser
{
    /**
     * @var AdminTokenService
     * @since 2.2.0
     */
    private $adminTokenService;

    /**
     * @param AdminTokenService $adminTokenService
     * @since 2.2.0
     */
    public function __construct(
        AdminTokenService $adminTokenService
    ) {
        $this->adminTokenService = $adminTokenService;
    }

    /**
     * Check if admin is inactive - if so, invalidate their tokens
     *
     * @param \Magento\User\Model\User $subject
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.2.0
     */
    public function afterSave(
        \Magento\User\Model\User $subject,
        \Magento\Framework\DataObject $object
    ) {
        $isActive = $object->getIsActive();
        if (isset($isActive) && $isActive == 0) {
            $this->adminTokenService->revokeAdminAccessToken($object->getId());
        }
        return $subject;
    }
}
