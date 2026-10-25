<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Integration\Plugin\Model;

use Magento\Integration\Model\AdminTokenService;
use Magento\User\Model\User;
use Magento\Framework\Model\AbstractModel;

/**
 * Plugin to delete admin tokens when admin becomes inactive
 */
class AdminUser
{
    /**
     * @var AdminTokenService
     */
    private $adminTokenService;

    /**
     * @param AdminTokenService $adminTokenService
     */
    public function __construct(
        AdminTokenService $adminTokenService
    ) {
        $this->adminTokenService = $adminTokenService;
    }

    public function afterSave(
        User $subject,
        AbstractModel $return
    ): AbstractModel {
        $isActive = $return->getIsActive();
        if ($isActive !== null && $isActive == 0) {
            $this->adminTokenService->revokeAdminAccessToken((int) $return->getId());
        }

        return $return;
    }

    public function afterDelete(User $subject, AbstractModel $return): AbstractModel
    {
        $this->adminTokenService->revokeAdminAccessToken((int) $return->getId());

        return $return;
    }
}
