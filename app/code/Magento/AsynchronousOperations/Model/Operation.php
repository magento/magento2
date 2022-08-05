<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\OperationStatusValidator;
use Magento\Framework\DataObject;

/**
 * Class Operation encapsulates methods for Operation Model Object
 */
class Operation extends DataObject implements OperationInterface
{
    /**
     * @var OperationStatusValidator
     */
    private $operationStatusValidator;

    /**
     * Operation constructor.
     *
     * @param OperationStatusValidator $operationStatusValidator
     * @param array $data
     */
    public function __construct(
        OperationStatusValidator $operationStatusValidator,
        array $data = []
    ) {
        $this->operationStatusValidator = $operationStatusValidator;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getBulkUuid()
    {
        return $this->getData(self::BULK_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBulkUuid($bulkId)
    {
        return $this->setData(self::BULK_ID, $bulkId);
    }

    /**
     * @inheritDoc
     */
    public function getTopicName()
    {
        return $this->getData(self::TOPIC_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setTopicName($topic)
    {
        return $this->setData(self::TOPIC_NAME, $topic);
    }

    /**
     * @inheritDoc
     */
    public function getSerializedData()
    {
        return $this->getData(self::SERIALIZED_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setSerializedData($serializedData)
    {
        return $this->setData(self::SERIALIZED_DATA, $serializedData);
    }

    /**
     * @inheritDoc
     */
    public function getResultSerializedData()
    {
        return $this->getData(self::RESULT_SERIALIZED_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setResultSerializedData($resultSerializedData)
    {
        return $this->setData(self::RESULT_SERIALIZED_DATA, $resultSerializedData);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        $this->operationStatusValidator->validate($status);
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getResultMessage()
    {
        return $this->getData(self::RESULT_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setResultMessage($resultMessage)
    {
        return $this->setData(self::RESULT_MESSAGE, $resultMessage);
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->getData(self::ERROR_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorCode($errorCode)
    {
        return $this->setData(self::ERROR_CODE, $errorCode);
    }

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
