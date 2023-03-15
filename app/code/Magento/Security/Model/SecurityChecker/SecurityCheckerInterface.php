<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

use Magento\Framework\Exception\SecurityViolationException;

/**
 * Interface for validation of reset password action
 *
 * @api
 * @since 100.1.0
 */
interface SecurityCheckerInterface
{
    /**
     * Perform security checks
     *
     * @param int $securityEventType
     * @param string|null $accountReference
     * @param int|null $longIp
     * @return void
     * @throws SecurityViolationException
     * @since 100.1.0
     */
    public function check($securityEventType, $accountReference = null, $longIp = null);
}
