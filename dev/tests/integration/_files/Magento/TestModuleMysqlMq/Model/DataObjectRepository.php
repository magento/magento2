<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMysqlMq\Model;

class DataObjectRepository
{
    /**
     * @param DataObject $dataObject
     * @param string $requiredParam
     * @param int|null $optionalParam
     * @return null
     */
    public function delayedOperation(
        \Magento\TestModuleMysqlMq\Model\DataObject $dataObject,
        $requiredParam,
        $optionalParam = null
    ) {
        $output = "Processed '{$dataObject->getEntityId()}'; "
            . "Required param '{$requiredParam}'; Optional param '{$optionalParam}'\n";
        file_put_contents($dataObject->getOutputPath(), $output);

        return null;
    }
}
