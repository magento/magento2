<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Model;

use Magento\Framework\Logger;
use Magento\Framework\Mail\Exception;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory;

/**
 * Class Notifier
 * @package Magento\Sales\Model
 */
abstract class AbstractNotifier extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param Sender $sender
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
     * @throws \Magento\Framework\Mail\Exception
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
        } catch (Exception $e) {
            $this->logger->logException($e);
            return false;
        }
        return true;
    }
}
