<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Model;

/**
 * AdminNotification Inbox interface
 *
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
     */
    public function getSeverities($severity = null);

    /**
     * Retrieve Latest Notice
     *
     * @return $this
     */
    public function loadLatestNotice();

    /**
     * Retrieve notice statuses
     *
     * @return array
     */
    public function getNoticeStatus();
}
