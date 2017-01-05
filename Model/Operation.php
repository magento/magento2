<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class Operation
 */
class Operation extends DataObject implements OperationInterface, ExtensibleDataInterface
{
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
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
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
