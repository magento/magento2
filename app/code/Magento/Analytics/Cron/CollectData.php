<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\ExportDataHandlerInterface;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Cron for data collection by a schedule for MBI.
 * @since 2.2.0
 */
class CollectData
{
    /**
     * Resource for the handling of a new data collection.
     *
     * @var ExportDataHandlerInterface
     * @since 2.2.0
     */
    private $exportDataHandler;

    /**
     * Resource which provides a status of subscription.
     *
     * @var SubscriptionStatusProvider
     * @since 2.2.0
     */
    private $subscriptionStatus;

    /**
     * @param ExportDataHandlerInterface $exportDataHandler
     * @param SubscriptionStatusProvider $subscriptionStatus
     * @since 2.2.0
     */
    public function __construct(
        ExportDataHandlerInterface $exportDataHandler,
        SubscriptionStatusProvider $subscriptionStatus
    ) {
        $this->exportDataHandler = $exportDataHandler;
        $this->subscriptionStatus = $subscriptionStatus;
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    public function execute()
    {
        if ($this->subscriptionStatus->getStatus() === SubscriptionStatusProvider::ENABLED) {
            $this->exportDataHandler->prepareExportData();
        }

        return true;
    }
}
