<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Token\RequestLog;

/**
 * OAuth token request log reader interface.
 * @since 2.0.3
 */
interface ReaderInterface
{
    /**
     * Get number of authentication failures for the specified user account.
     *
     * @param string $userName
     * @param int $userType
     * @return int
     * @since 2.0.3
     */
    public function getFailuresCount($userName, $userType);
}
