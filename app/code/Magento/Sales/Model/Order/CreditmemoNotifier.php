<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class CreditmemoNotifier
 * @package Magento\Sales\Model
 * @since 2.0.0
 */
class CreditmemoNotifier extends \Magento\Sales\Model\AbstractNotifier
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
     * @var CreditmemoSender
     * @since 2.0.0
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param CreditmemoSender $sender
     * @since 2.0.0
     */
    public function __construct(
        CollectionFactory $historyCollectionFactory,
        Logger $logger,
        CreditmemoSender $sender
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logger = $logger;
        $this->sender = $sender;
    }
}
