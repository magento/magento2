<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel\System\Message;

use Magento\AdminNotification\Model;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Notification\MessageList;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 *
 * @package Magento\AdminNotification\Model\ResourceModel\System\Message
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    /**
     * System message list
     *
     * @var MessageList
     */
    protected $_messageList; //phpcs:ignore

    /**
     * Number of messages by severity
     *
     * @var array
     */
    protected $_countBySeverity = []; //phpcs:ignore

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param MessageList $messageList
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        MessageList $messageList,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_messageList = $messageList;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Resource collection initialization
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init(
            Model\System\Message::class,
            Model\ResourceModel\System\Message::class
        );
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initSelect(): void //phpcs:ignore
    {
        parent::_initSelect();
        $this->addOrder('severity', self::SORT_ORDER_ASC)->addOrder('created_at');
    }

    /**
     * Initialize system messages after load
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function _afterLoad() //phpcs:ignore
    {
        foreach ($this->_items as $key => $item) {
            $message = $this->_messageList->getMessageByIdentity($item->getIdentity());
            if ($message) {
                $item->setText($message->getText());
                if (array_key_exists($message->getSeverity(), $this->_countBySeverity)) {
                    $this->_countBySeverity[$message->getSeverity()]++;
                } else {
                    $this->_countBySeverity[$message->getSeverity()] = 1;
                }
            } else {
                unset($this->_items[$key]);
            }
        }
    }

    /**
     * Set message severity filter
     *
     * @param int $severity
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->addFieldToFilter('severity', ['eq' => $severity * 1]);
        return $this;
    }

    /**
     * Retrieve number of messages by severity
     *
     * @param int $severity
     * @return int
     */
    public function getCountBySeverity($severity): int
    {
        return isset($this->_countBySeverity[$severity]) ? $this->_countBySeverity[$severity] : 0;
    }
}
