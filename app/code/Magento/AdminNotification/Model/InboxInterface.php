<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model;

/**
 * AdminNotification Inbox interface
 *
 * @author Magento Core Team <core@magentocommerce.com>
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
