<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Customer log model.
 *
 * Contains customer log data.
 * @since 2.0.0
 */
class Log
{
    /**
     * Customer ID.
     *
     * @var int
     * @since 2.0.0
     */
    protected $customerId;

    /**
     * Date and time of customer's last login.
     *
     * @var string
     * @since 2.0.0
     */
    protected $lastLoginAt;

    /**
     * Date and time of customer's last logout.
     *
     * @var string
     * @since 2.0.0
     */
    protected $lastVisitAt;

    /**
     * Date and time of customer's last visit.
     *
     * @var string
     * @since 2.0.0
     */
    protected $lastLogoutAt;

    /**
     * @param int $customerId
     * @param string $lastLoginAt
     * @param string $lastVisitAt
     * @param string $lastLogoutAt
     * @since 2.0.0
     */
    public function __construct($customerId = null, $lastLoginAt = null, $lastVisitAt = null, $lastLogoutAt = null)
    {
        $this->customerId = $customerId;
        $this->lastLoginAt = $lastLoginAt;
        $this->lastVisitAt = $lastVisitAt;
        $this->lastLogoutAt = $lastLogoutAt;
    }

    /**
     * Retrieve customer id
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Retrieve last login date as string
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Retrieve last visit date as string
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }

    /**
     * Retrieve last logout date as string
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastLogoutAt()
    {
        return $this->lastLogoutAt;
    }
}
