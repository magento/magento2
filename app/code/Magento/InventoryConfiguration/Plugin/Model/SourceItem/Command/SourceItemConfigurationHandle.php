<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Plugin\Model\SourceItem\Command;

use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\InventoryConfiguration\Api\SourceItemConfigurationSaveInterface;
use Psr\Log\LoggerInterface;

class SourceItemConfigurationHandle
{
    /**
     * @var SourceItemConfigurationSaveInterface
     */
    protected $saveNotificationQty;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceItemNotificationHandle constructor.
     *
     * @param LoggerInterface $logger
     * @param SourceItemConfigurationSaveInterface $saveNotificationQty
     */
    public function __construct(
        LoggerInterface $logger,
        SourceItemConfigurationSaveInterface $saveNotificationQty
    )
    {
        $this->saveNotificationQty = $saveNotificationQty;
        $this->logger = $logger;
    }

    public function aroundExecute(SourceItemsSave $subject, callable $proceed, array $sourceItems)
    {
        $result = $proceed($sourceItems);
        $this->saveNotificationQty->saveSourceItemConfiguration($sourceItems);;

        $this->notify();

        return $result;
    }

    protected function notify()
    {

    }
}