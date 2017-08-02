<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class OrderNotifier
 * @package Magento\Sales\Model
 * @since 2.0.0
 */
class OrderNotifier extends \Magento\Sales\Model\AbstractNotifier
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
     * @var OrderSender
     * @since 2.0.0
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param OrderSender $sender
     * @since 2.0.0
     */
    public function __construct(
        CollectionFactory $historyCollectionFactory,
        Logger $logger,
        OrderSender $sender
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logger = $logger;
        $this->sender = $sender;
    }
}
