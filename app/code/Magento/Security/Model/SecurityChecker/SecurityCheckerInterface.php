<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

/**
 * Interface SecurityCheckerInterface
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
