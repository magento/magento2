<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

/**
 * Interface for validation of reset password action
 *
 * @api
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
     * @throws \Magento\Framework\Exception\SecurityViolationException
     */
    public function check($securityEventType, $accountReference = null, $longIp = null);
}
