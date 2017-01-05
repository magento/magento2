<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Validators;

use Magento\Framework\DataObject;

/**
 * Validates Signifyd Case id field.
 */
class CaseDataValidator
{
    /**
     * Checks if data object contains Signifyd Case id.
     * @param DataObject $data
     * @return bool
     */
    public function validate(DataObject $data)
    {
        if (empty($data->getData('caseId'))) {
            return false;
        }

        return true;
    }
}
