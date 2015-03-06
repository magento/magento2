<?php
/**
 * Created by PhpStorm.
 * User: akaplya
 * Date: 05.03.15
 * Time: 19:33
 */

class LastLoggedIn
{
    protected $logger;
    protected $dateTime;

    public function __construct(
        \Magento\Customer\Model\Logger $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    public function logLastLoginAt(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->log(
            $observer->getEvent()->getCustomer()->getId(), ['last_login_at' => $this->dateTime->now()]
        );
    }

    public function logLastLogoutAt(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->log(
            $observer->getEvent()->getCustomer()->getId(), ['last_logout_at' => $this->dateTime->now()]
        );
    }
}