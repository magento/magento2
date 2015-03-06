<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Class Log
 */
class Log
{
    protected $customerId;
    protected $lastLoginAt;
    protected $lastLogoutAt;
    protected $lastVisitAt;

    public function __construct($customerId, $lastLoginAt, $lastLogoutAt, $lastVisitAt)
    {
        $this->customerId = $customerId;
        $this->lastLoginAt = $lastLoginAt;
        $this->lastLogoutAt = $lastLogoutAt;
        $this->lastVisitAt = $lastVisitAt;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    public function getLastLogoutAt()
    {
        return $this->lastLogoutAt;
    }

    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }
}