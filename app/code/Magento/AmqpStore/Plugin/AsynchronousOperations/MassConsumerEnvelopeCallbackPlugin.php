<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AmqpStore\Plugin\AsynchronousOperations;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use PhpAmqpLib\Wire\AMQPTable;
use Magento\Framework\Amqp\Queue;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallback;
use Psr\Log\LoggerInterface;

/**
 * Plugin to get 'store_id' from the new custom header 'store_id' in amqp
 * 'application_headers' properties and setCurrentStore by value 'store_id'.
 */
class MassConsumerEnvelopeCallbackPlugin
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\MessageQueue\EnvelopeFactory
     */
    private $envelopeFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\MessageQueue\EnvelopeFactory $envelopeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EnvelopeFactory $envelopeFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->envelopeFactory = $envelopeFactory;
        $this->logger = $logger;
    }

    /**
     * @param MassConsumerEnvelopeCallback $subject
     * @param EnvelopeInterface $message
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(MassConsumerEnvelopeCallback $subject, EnvelopeInterface $message)
    {
        $amqpProperties = $message->getProperties();
        if (isset($amqpProperties['application_headers'])) {
            $headers = $amqpProperties['application_headers'];
            if ($headers instanceof AMQPTable) {
                $headers = $headers->getNativeData();
            }
            if (isset($headers['store_id'])) {
                $storeId = $headers['store_id'];
                try {
                    $currentStoreId = $this->storeManager->getStore()->getId();
                } catch (NoSuchEntityException $e) {
                    $this->logger->error(
                        sprintf("Can't set currentStoreId during processing queue. Error %s.", $e->getMessage())
                    );
                    return null;
                }
                if (isset($storeId) && $storeId !== $currentStoreId) {
                        $this->storeManager->setCurrentStore($storeId);
                }
            }
        }
        return [$message];
    }
}
