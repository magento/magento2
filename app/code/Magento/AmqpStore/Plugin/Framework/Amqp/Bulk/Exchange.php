<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AmqpStore\Plugin\Framework\Amqp\Bulk;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Wire\AMQPTable;
use Magento\Framework\Amqp\Bulk\Exchange as SubjectExchange;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to set 'store_id' to the new custom header 'store_id' in amqp
 * 'application_headers'.
 */
class Exchange
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
     * Exchange constructor.
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
     * Set current store_id in amqpProperties['application_headers']
     * so consumer may check store_id and execute operation in correct store scope.
     * Prevent publishing inconsistent messages because of store_id not defined or wrong.
     *
     * @param SubjectExchange $subject
     * @param string $topic
     * @param EnvelopeInterface[] $envelopes
     * @return array
     * @throws AMQPInvalidArgumentException
     * @throws \LogicException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEnqueue(SubjectExchange $subject, $topic, array $envelopes)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $errorMessage = sprintf(
                "Can't get current storeId and inject to amqp message. Error %s.",
                $e->getMessage()
            );
            $this->logger->error($errorMessage);
            throw new \LogicException($errorMessage);
        }

        $updatedEnvelopes = [];
        foreach ($envelopes as $envelope) {
            $properties = $envelope->getProperties();
            if (!isset($properties)) {
                $properties = [];
            }
            if (isset($properties['application_headers'])) {
                $headers = $properties['application_headers'];
                if ($headers instanceof AMQPTable) {
                    try {
                        $headers->set('store_id', $storeId);
                    } catch (AMQPInvalidArgumentException $ea) {
                        $errorMessage = sprintf("Can't set storeId to amqp message. Error %s.", $ea->getMessage());
                        $this->logger->error($errorMessage);
                        throw new AMQPInvalidArgumentException($errorMessage);
                    }
                    $properties['application_headers'] = $headers;
                }
            } else {
                $properties['application_headers'] = new AMQPTable(['store_id' => $storeId]);
            }
            $updatedEnvelopes[] = $this->envelopeFactory->create(
                [
                    'body' => $envelope->getBody(),
                    'properties' => $properties
                ]
            );
        }
        if (!empty($updatedEnvelopes)) {
            $envelopes = $updatedEnvelopes;
        }
        return [$topic, $envelopes];
    }
}
