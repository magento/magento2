<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Customer log model.
 *
 * Contains customer log data.
 */
class Log
{
    /**
     * Customer ID.
     *
     * @var int
     */
    protected $customerId;

    /**
     * Date and time of customer's last login.
     *
     * @var string
     */
    protected $lastLoginAt;

    /**
     * Date and time of customer's last logout.
     *
     * @var string
     */
    protected $lastVisitAt;

    /**
     * Date and time of customer's last visit.
     *
     * @var string
     */
    protected $lastLogoutAt;

    /**
     * @param int $customerId
     * @param string $lastLoginAt
     * @param string $lastVisitAt
     * @param string $lastLogoutAt
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
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Retrieve last login date as string
     *
     * @return string
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Retrieve last visit date as string
     *
     * @return string
     */
    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }

    /**
     * Retrieve last logout date as string
     *
     * @return string
     */
    public function getLastLogoutAt()
    {
        return $this->lastLogoutAt;
    }
}
