<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Reports\Model\Event;

/**
 * Reports Event observer model
 */
class SendfriendProductObserver implements ObserverInterface
{
    /**
     * @var EventSaver
     */
    protected $eventSaver;

    /**
     * @var \Magento\Reports\Model\ReportStatus
     */
    private $reportStatus;

    /**
     * @param EventSaver $eventSaver
     */
    public function __construct(
        EventSaver $eventSaver,
        \Magento\Reports\Model\ReportStatus $reportStatus
    ) {
        $this->eventSaver = $eventSaver;
        $this->reportStatus = $reportStatus;
    }

    /**
     * Send Product link to friends action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_SEND)) {
            return;
        }

        $this->eventSaver->save(
            Event::EVENT_PRODUCT_SEND,
            $observer->getEvent()->getProduct()->getId()
        );
    }
}
