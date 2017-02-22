<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Token\RequestLog;

/**
 * OAuth token request log writer interface.
 */
interface WriterInterface
{
    /**
     * Reset number of authentication failures for the specified user account.
     *
     * @param string $userName
     * @param int $userType
     * @return void
     */
    public function resetFailuresCount($userName, $userType);

    /**
     * Increment number of authentication failures for the specified user account.
     *
     * @param string $userName
     * @param int $userType
     * @return void
     */
    public function incrementFailuresCount($userName, $userType);

    /**
     * Clear expired authentication failure logs.
     *
     * @return void
     */
    public function clearExpiredFailures();
}
