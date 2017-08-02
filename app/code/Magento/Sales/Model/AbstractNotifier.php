<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class Notifier
 * @package Magento\Sales\Model
 * @since 2.0.0
 */
abstract class AbstractNotifier extends \Magento\Framework\Model\AbstractModel
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
     * @var Sender
     * @since 2.0.0
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param Sender $sender
     * @since 2.0.0
     */
    public function __construct(
        CollectionFactory $historyCollectionFactory,
        Logger $logger,
        Sender $sender
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logger = $logger;
        $this->sender = $sender;
    }

    /**
     * Notify user
     *
     * @param AbstractModel $model
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     * @since 2.0.0
     */
    public function notify(\Magento\Sales\Model\AbstractModel $model)
    {
        try {
            $this->sender->send($model);
            if (!$model->getEmailSent()) {
                return false;
            }
            $historyItem = $this->historyCollectionFactory->create()
                ->getUnnotifiedForInstance($model);
            if ($historyItem) {
                $historyItem->setIsCustomerNotified(1);
                $historyItem->save();
            }
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->logger->critical($e);
            return false;
        }
        return true;
    }
}
