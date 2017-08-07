<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Token\RequestLog;

/**
 * OAuth token request log writer interface.
 * @since 2.0.3
 */
interface WriterInterface
{
    /**
     * Reset number of authentication failures for the specified user account.
     *
     * @param string $userName
     * @param int $userType
     * @param return void
     * @return void
     * @since 2.0.3
     */
    public function resetFailuresCount($userName, $userType);

    /**
     * Increment number of authentication failures for the specified user account.
     *
     * @param string $userName
     * @param int $userType
     * @param return void
     * @return void
     * @since 2.0.3
     */
    public function incrementFailuresCount($userName, $userType);

    /**
     * Clear expired authentication failure logs.
     *
     * @return void
     * @since 2.0.3
     */
    public function clearExpiredFailures();
}
