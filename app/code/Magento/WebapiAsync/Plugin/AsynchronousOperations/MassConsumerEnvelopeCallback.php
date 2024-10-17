<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\AsynchronousOperations;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\AsynchronousOperations\Model\MassConsumerEnvelopeCallback as SubjectMassConsumerEnvelopeCallback;
use Psr\Log\LoggerInterface;

/**
 * Plugin to get 'store_id' from the message and setCurrentStore by value 'store_id'.
 */
class MassConsumerEnvelopeCallback
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $jsonHelper;

    /**
     * @param StoreManagerInterface $storeManager
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param Json $jsonHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        Json $jsonHelper,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
    }

    /**
     * Check if message contains 'store_id' and use it to setCurrentStore
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
        /** @var OperationInterface $operation */
        $operation = $this->messageEncoder->decode(AsyncConfig::SYSTEM_TOPIC_NAME, $message->getBody());
        $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $operation);
        $data = $this->jsonHelper->unserialize($operation->getSerializedData());
        if (isset($data['store_id'])) {
            $storeId = $data['store_id'];
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
            if ($storeId !== $currentStoreId) {
                $this->storeManager->setCurrentStore($storeId);
            }
        }
        $proceed($message);
        if (isset($storeId, $currentStoreId) && $storeId !== $currentStoreId) {
            $this->storeManager->setCurrentStore($currentStoreId);//restore original store value
        }
    }
}
