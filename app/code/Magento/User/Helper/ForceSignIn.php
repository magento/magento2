<?php
/************************************************************************
 *
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\User\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Security\Model\ResourceModel\AdminSessionInfo;
use Magento\Security\Model\AdminSessionInfo as AdminSessionInfoModel;

/**
 * Update admin user session status to logged out
 */
class ForceSignIn extends AbstractHelper
{
    /**
     * @param Context $context
     * @param AdminSessionInfo $adminSessionInfo
     */
    public function __construct(
        Context $context,
        private readonly AdminSessionInfo $adminSessionInfo
    ) {
        parent::__construct($context);
    }

    /**
     * Update admin_user_session status to logged out
     *
     * @param int $userId
     * @throws LocalizedException
     */
    public function updateAdminSessionStatus($userId): void
    {
        try {
            $this->adminSessionInfo->updateStatusByUserId(
                AdminSessionInfoModel::LOGGED_OUT,
                $userId,
                [AdminSessionInfoModel::LOGGED_IN]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
