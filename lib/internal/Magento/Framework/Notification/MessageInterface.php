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
 *
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getIdentity();

    /**
     * Check whether
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisplayed();

    /**
     * Retrieve message text
     *
     * @return string
     * @since 2.0.0
     */
    public function getText();

    /**
     * Retrieve message severity
     *
     * @return int
     * @since 2.0.0
     */
    public function getSeverity();
}
