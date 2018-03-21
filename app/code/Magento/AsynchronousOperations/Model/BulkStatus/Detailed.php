<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\BulkStatus\DetailedInterface;
use Magento\AsynchronousOperations\Api\Data\OperationDetailsInterface;
use Magento\AsynchronousOperations\Model\BulkSummary;

class Detailed extends BulkSummary implements DetailedInterface
{
    /**
     * @inheritDoc
     */
    public function getOperationsList()
    {
        return $this->getData(self::OPERATIONS_LIST);
    }

    /**
     * @inheritDoc
     */
    public function setOperationsList($operationStatusList)
    {
        return $this->setData(self::OPERATIONS_LIST, $operationStatusList);
    }

    /**
     * @inheritDoc
     */
    public function getOperationsCounter()
    {
        return $this->getData(self::OPERATIONS_COUNTER);
    }

    /**
     * @inheritDoc
     */
    public function setOperationsCounter(OperationDetailsInterface $operationDetails)
    {
        return $this->setData(self::OPERATIONS_COUNTER, $operationDetails);
    }
}
