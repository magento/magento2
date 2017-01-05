<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\DataObject;

/**
 * Class BulkSummary
 */
class BulkSummary extends DataObject implements BulkSummaryInterface, \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @inheritDoc
     */
    public function getBulkId()
    {
        return $this->getData(self::BULK_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBulkId($bulkUuid)
    {
        return $this->setData(self::BULK_ID, $bulkUuid);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getStartTime()
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setStartTime($timestamp)
    {
        return $this->setData(self::START_TIME, $timestamp);
    }

    /**
     * @inheritDoc
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * @inheritDoc
     */
    public function getOperationCount()
    {
        return $this->getData(self::OPERATION_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setOperationCount($operationCount)
    {
        return $this->setData(self::OPERATION_COUNT, $operationCount);
    }

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
