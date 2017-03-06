<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\ExportDataHandler;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Cron for data collection by a schedule for MBI.
 */
class CollectData
{
    /**
     * Resource for the handling of a new data collection.
     *
     * @var ExportDataHandler
     */
    private $exportDataHandler;

    /**
     * Resource which provides a status of subscription.
     *
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatus;

    /**
     * @param ExportDataHandler $exportDataHandler
     * @param SubscriptionStatusProvider $subscriptionStatus
     */
    public function __construct(
        ExportDataHandler $exportDataHandler,
        SubscriptionStatusProvider $subscriptionStatus
    ) {
        $this->exportDataHandler = $exportDataHandler;
        $this->subscriptionStatus = $subscriptionStatus;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->subscriptionStatus->getStatus() === SubscriptionStatusProvider::ENABLED) {
            $this->exportDataHandler->prepareExportData();
        }

        return true;
    }
}
