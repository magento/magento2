<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Security\Model\SecurityChecker;

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
     * @throws \Magento\Framework\Exception\SecurityViolationException
     * @since 100.1.0
     */
    public function check($securityEventType, $accountReference = null, $longIp = null);
}
