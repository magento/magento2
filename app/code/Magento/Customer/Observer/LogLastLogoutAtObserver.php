<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Observer;

use Magento\Customer\Model\Logger;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Customer log observer.
 * @since 2.0.0
 */
class LogLastLogoutAtObserver implements ObserverInterface
{
    /**
     * Logger of customer's log data.
     *
     * @var Logger
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @param Logger $logger
     * @since 2.0.0
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handler for 'customer_logout' event.
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        $this->logger->log(
            $observer->getEvent()->getCustomer()->getId(),
            ['last_logout_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)]
        );
    }
}
