<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\Operation\Status;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationStatus\ShortInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class OperationShortDetails
 */
class Short extends DataObject implements ShortInterface, ExtensibleDataInterface
{
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(OperationInterface::ID);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(OperationInterface::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getResultMessage()
    {
        return $this->getData(OperationInterface::RESULT_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->getData(OperationInterface::ERROR_CODE);
    }
}
