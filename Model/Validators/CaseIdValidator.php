<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Validators;

/**
 * Validates Signifyd Case id field.
 */
class CaseIdValidator
{
    /**
     * Checks if data object contains Signifyd Case id.
     * @param array $data
     * @return bool
     */
    public function validate(array $data)
    {
        if (empty($data['caseId'])) {
            return false;
        }

        return true;
    }
}
