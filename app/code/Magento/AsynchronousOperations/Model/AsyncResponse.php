<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

class AsyncResponse extends DataObject implements AsyncResponseInterface, ExtensibleDataInterface
{
    /**
     * @inheritDoc
     */
    public function getBulkUuid()
    {
        return $this->getData(self::BULK_UUID);
    }

    /**
     * @inheritDoc
     */
    public function setBulkUuid($bulkUuid)
    {
        return $this->setData(self::BULK_UUID, $bulkUuid);
    }

    /**
     * @inheritDoc
     */
    public function getRequestItems()
    {
        return $this->getData(self::REQUEST_ITEMS);
    }

    /**
     * @inheritDoc
     */
    public function setRequestItems($requestItems)
    {
        return $this->setData(self::REQUEST_ITEMS, $requestItems);
    }

    /**
     * @inheritdoc
     */
    public function setErrors($isErrors = false)
    {
        return $this->setData(self::ERRORS, $isErrors);
    }

    /**
     * @inheritdoc
     */
    public function isErrors()
    {
        return $this->getData(self::ERRORS);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
