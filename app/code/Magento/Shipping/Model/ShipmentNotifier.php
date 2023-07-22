<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Sales\Model\AbstractNotifier;
use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class ShipmentNotifier
 * @package Magento\Shipping\Model
 */
class ShipmentNotifier extends AbstractNotifier
{
    /**
     * @var CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ShipmentSender
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param ShipmentSender $sender
     */
    public function __construct(
        CollectionFactory $historyCollectionFactory,
        Logger $logger,
        ShipmentSender $sender
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logger = $logger;
        $this->sender = $sender;
    }
}
