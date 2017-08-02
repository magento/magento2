<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class ShipmentNotifier
 * @package Magento\Shipping\Model
 * @since 2.0.0
 */
class ShipmentNotifier extends \Magento\Sales\Model\AbstractNotifier
{
    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $historyCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var ShipmentSender
     * @since 2.0.0
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param ShipmentSender $sender
     * @since 2.0.0
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
