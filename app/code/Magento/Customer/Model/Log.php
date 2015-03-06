<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    public function __construct($customerId=null, $lastLoginAt, $lastVisitAt, $lastLogoutAt)
    {
        $this->customerId = $customerId;
        $this->lastLoginAt = $lastLoginAt;
        $this->lastVisitAt = $lastVisitAt;
        $this->lastLogoutAt = $lastLogoutAt;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * @return string
     */
    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }

    /**
     * @return string
     */
    public function getLastLogoutAt()
    {
        return $this->lastLogoutAt;
    }
}
