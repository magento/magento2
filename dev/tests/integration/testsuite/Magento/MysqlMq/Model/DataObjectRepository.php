<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

class DataObjectRepository
{
    /**
     * @param DataObject $dataObject
     * @param string $requiredParam
     * @param int|null $optionalParam
     * @return bool
     */
    public function delayedOperation(
        \Magento\MysqlMq\Model\DataObject $dataObject,
        $requiredParam,
        $optionalParam = null
    ) {
        echo "Processed '{$dataObject->getEntityId()}'; "
            . "Required param '{$requiredParam}'; Optional param '{$optionalParam}'\n";
        return true;
    }
}
