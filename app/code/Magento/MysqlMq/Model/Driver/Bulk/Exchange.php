<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver\Bulk;

use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Used to send messages in bulk in MySQL queue.
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @var QueueManagement
     */
    private $queueManagement;

    /**
     * Initialize dependencies.
     *
     * @param MessageQueueConfig $messageQueueConfig
     * @param QueueManagement $queueManagement
     */
    public function __construct(MessageQueueConfig $messageQueueConfig, QueueManagement $queueManagement)
    {
        $this->messageQueueConfig = $messageQueueConfig;
        $this->queueManagement = $queueManagement;
    }

    /**
     * @inheritdoc
     */
    public function enqueue($topic, array $envelopes)
    {
        $queueNames = [];
        $exchanges = $this->messageQueueConfig->getExchanges();
        foreach ($exchanges as $exchange) {
          // @todo Is there a more reliable way to identify MySQL exchanges?
          if ($exchange->getConnection() == 'db') {
            foreach ($exchange->getBindings() as $binding) {
              // This only supports exact matching of topics.
              if ($binding->getTopic() == $topic) {
                $queueNames[] = $binding->getDestination();
              }
            }
          }
        }
        $messages = array_map(
            function ($envelope) {
                return $envelope->getBody();
            },
            $envelopes
        );
        $this->queueManagement->addMessagesToQueues($topic, $messages, $queueNames);

        return null;
    }
}
