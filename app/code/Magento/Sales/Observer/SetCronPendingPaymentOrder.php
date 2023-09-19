<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Observer;

use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class SetCronPendingPaymentOrder implements ObserverInterface
{
    public const XML_PATH_ORDER_DELETE_PENDING = 'sales/orders/delete_pending_after';

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool
     */
    protected $isStart = false;

    /**
     * @param ScheduleFactory $scheduleFactory
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScheduleFactory $scheduleFactory,
        DateTime $dateTime,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Set cron for order pending payment
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->isStart) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        if (!$order->getId() || $order->getStatus() !== Order::STATE_PENDING_PAYMENT) {
            return $this;
        }

        $lifetime = $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_DELETE_PENDING,
            ScopeInterface::SCOPE_WEBSITES,
            $order->getStore()->getWebsite()->getId()
        );
        $currentTime = $this->dateTime->gmtTimestamp();
        try {
            $schedule = $this->scheduleFactory->create()
                ->setJobCode('sales_clean_orders')
                ->setStatus(Schedule::STATUS_PENDING)
                ->setCreatedAt(date('Y-m-d H:i:s', $currentTime))
                ->setScheduledAt(date('Y-m-d H:i', $currentTime + $lifetime * 60));

            $schedule->save();
            $this->isStart = true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
