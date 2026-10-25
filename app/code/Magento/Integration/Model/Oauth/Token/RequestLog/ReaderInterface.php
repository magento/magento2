<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Model\Oauth\Token\RequestLog;

/**
 * OAuth token request log reader interface.
 *
 * @api
 */
interface ReaderInterface
{
    /**
     * Get number of authentication failures for the specified user account.
     *
     * @param string $userName
     * @param int $userType
     * @return int
     */
    public function getFailuresCount($userName, $userType);
}
