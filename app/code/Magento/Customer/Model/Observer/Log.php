<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

/**
 * Customer log observer.
 */
class Log
{
    /**
     * Logger of customer's log data.
     *
     * @var \Magento\Customer\Model\Logger
     */
    protected $logger;

    /**
     * Date formats converter.
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Customer\Model\Logger $logger
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Customer\Model\Logger $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    /**
     * Handler for 'customer_login' event.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function logLastLoginAt(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->log(
            $observer->getEvent()->getCustomer()->getId(),
            ['last_login_at' => $this->dateTime->now()]
        );
    }

    /**
     * Handler for 'customer_logout' event.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function logLastLogoutAt(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->log(
            $observer->getEvent()->getCustomer()->getId(),
            ['last_logout_at' => $this->dateTime->now()]
        );
    }
}
