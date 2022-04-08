<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Exception;
use Magento\AdminAdobeIms\Model\FlushUserTokens;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\AdminTokenService;

class RevokeAdminAccessTokenPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var FlushUserTokens
     */
    private FlushUserTokens $flushUserTokens;

    /**
     * @param ImsConfig $imsConfig
     * @param FlushUserTokens $flushUserTokens
     */
    public function __construct(
        ImsConfig $imsConfig,
        FlushUserTokens $flushUserTokens
    ) {
        $this->imsConfig = $imsConfig;
        $this->flushUserTokens = $flushUserTokens;
    }

    /**
     * Get access token(s) by admin id and logout user from Adobe IMS
     *
     * @param AdminTokenService $subject
     * @param bool $result
     * @param int $adminId
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRevokeAdminAccessToken(
        AdminTokenService $subject,
        bool $result,
        int $adminId
    ): bool {

        if ($this->imsConfig->enabled() !== true) {
            return $result;
        }

        try {
            $this->flushUserTokens->execute($adminId);
        } catch (Exception $exception) {
            throw new LocalizedException(__('The tokens couldn\'t be revoked.'), $exception);
        }

        return $result;
    }
}
