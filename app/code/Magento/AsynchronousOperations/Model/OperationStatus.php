<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\SummaryOperationStatusInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class OperationShortDetails
 */
class OperationStatus extends DataObject implements SummaryOperationStatusInterface, ExtensibleDataInterface
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
