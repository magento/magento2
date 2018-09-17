<?php
/**
 * System message
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Notification;

/**
 * Interface for system messages
 *
 * Interface MessageInterface
 */
interface MessageInterface
{
    const SEVERITY_CRITICAL = 1;

    const SEVERITY_MAJOR = 2;

    const SEVERITY_MINOR = 3;

    const SEVERITY_NOTICE = 4;

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity();

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed();

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText();

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity();
}
