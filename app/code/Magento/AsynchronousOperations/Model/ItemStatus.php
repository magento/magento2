<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\ItemStatusInterface;
use Magento\Framework\DataObject;

class ItemStatus extends DataObject implements ItemStatusInterface
{
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getDataHash()
    {
        return $this->getData(self::DATA_HASH);
    }

    /**
     * @inheritDoc
     */
    public function setDataHash($hash)
    {
        return $this->setData(self::DATA_HASH, $hash);
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
    public function setStatus($status = self::STATUS_ACCEPTED)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return $this->getData(self::ERROR_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorMessage($errorMessage = null)
    {
        if ($errorMessage instanceof \Exception) {
            $errorMessage = $errorMessage->getMessage();
        }

        return $this->setData(self::ERROR_MESSAGE, $errorMessage);
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
    public function setErrorCode($errorCode = null)
    {
        if ($errorCode instanceof \Exception) {
            $errorCode = $errorCode->getCode();
        }

        return $this->setData(self::ERROR_CODE, (int) $errorCode);
    }
}
