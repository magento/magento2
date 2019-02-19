<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface;
use Magento\AsynchronousOperations\Model\BulkSummary;

class Detailed extends BulkSummary implements DetailedBulkOperationsStatusInterface
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
}
