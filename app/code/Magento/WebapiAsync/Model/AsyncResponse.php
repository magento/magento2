<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Model;

use Magento\WebapiAsync\Api\Data\AsyncResponseInterface;
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
    public function setIsErrors($isErrors = false)
    {
        return $this->setData(self::IS_ERRORS, $isErrors);
    }

    /**
     * @inheritdoc
     */
    public function getIsErrors()
    {
        return $this->getData(self::IS_ERRORS);
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
        \Magento\WebapiAsync\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
