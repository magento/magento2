<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model;

/**
 * AdminNotification Inbox interface
 *
 * @package Magento\AdminNotification\Model
 * @author Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
interface InboxInterface
{
    /**
     * Retrieve Severity collection array
     *
     * @param int|null $severity
     * @return array|string|null
     * @api
     */
    public function getSeverities($severity = null);

    /**
     * Retrieve Latest Notice
     *
     * @return $this
     * @api
     */
    public function loadLatestNotice();

    /**
     * Retrieve notice statuses
     *
     * @return array
     * @api
     */
    public function getNoticeStatus();
}
