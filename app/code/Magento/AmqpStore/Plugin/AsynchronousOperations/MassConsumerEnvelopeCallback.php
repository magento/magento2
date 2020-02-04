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
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallback as SubjectMassConsumerEnvelopeCallback;
use Psr\Log\LoggerInterface;

/**
 * Plugin to get 'store_id' from the new custom header 'store_id' in amqp
 * 'application_headers' properties and setCurrentStore by value 'store_id'.
 */
class MassConsumerEnvelopeCallback
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EnvelopeFactory $envelopeFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
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
     * Check if amqpProperties['application_headers'] have 'store_id' and use it to setCurrentStore
     * Restore original store value in consumer process after execution.
     * Reject queue messages because of wrong store_id.
     *
     * @param SubjectMassConsumerEnvelopeCallback $subject
     * @param callable $proceed
     * @param EnvelopeInterface $message
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        SubjectMassConsumerEnvelopeCallback $subject,
        callable $proceed,
        EnvelopeInterface $message
    ) {
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
                        sprintf(
                            "Can't set currentStoreId during processing queue. Message rejected. Error %s.",
                            $e->getMessage()
                        )
                    );
                    $subject->getQueue()->reject($message, false, $e->getMessage());
                    return;
                }
                if (isset($storeId) && $storeId !== $currentStoreId) {
                    $this->storeManager->setCurrentStore($storeId);
                }
            }
        }
        $proceed($message);
        if (isset($storeId, $currentStoreId) && $storeId !== $currentStoreId) {
            $this->storeManager->setCurrentStore($currentStoreId);//restore original store value
        }
    }
}
