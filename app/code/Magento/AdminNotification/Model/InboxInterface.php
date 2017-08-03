<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model;

/**
 * AdminNotification Inbox interface
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
interface InboxInterface
{
    /**
     * Retrieve Severity collection array
     *
     * @param int|null $severity
     * @return array|string|null
     * @api
     * @since 2.0.0
     */
    public function getSeverities($severity = null);

    /**
     * Retrieve Latest Notice
     *
     * @return $this
     * @api
     * @since 2.0.0
     */
    public function loadLatestNotice();

    /**
     * Retrieve notice statuses
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getNoticeStatus();
}
