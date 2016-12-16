<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

class CalculatedStatusSql
{
    /**
     * Get sql to calculate bulk status
     *
     * @param string $operationTableName
     * @return \Zend_Db_Expr
     */
    public function get($operationTableName)
    {
        return new \Zend_Db_Expr(
            '(IF(
                (SELECT count(*)
                    FROM ' . $operationTableName . '
                    WHERE bulk_uuid = main_table.uuid
                ) = 0,
                ' . BulkSummaryInterface::NOT_STARTED . ',
                (SELECT MAX(status) FROM ' . $operationTableName . ' WHERE bulk_uuid = main_table.uuid)
            ))'
        );
    }
}
