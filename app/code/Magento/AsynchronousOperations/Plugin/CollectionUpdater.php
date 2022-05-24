<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Plugin;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class for bulk operation collection updates for operation key unique values
 */
class CollectionUpdater
{
    /**
     * Adds id value in operation_key in case of bulk operation
     *
     * @param AbstractDb $subject
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetData(AbstractDb $subject, $result)
    {
        if (is_array($result) && !empty($result) &&
            $subject->getResource()->getMainTable() == "magento_operation" &&
            $subject->getResource()->getTable('magento_bulk') == "magento_bulk"
        ) {
            foreach ($result as $key => $row) {
                $result[$key][OperationInterface::ID] = $row['id'];
            }
        }

        return $result;
    }
}
