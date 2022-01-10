<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Model\Plugin\Store\Model\Validation;

use Magento\Store\Model\Validation\StoreCodeValidator as Subject;

/**
 * Suppress using code parsed from url first path for api calls.
 */
class StoreCodeValidator
{
    /**
     * Validate if store code parsed incorrectly.
     *
     * @param Subject $subject
     * @param bool $result
     * @param string $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsValid(Subject $subject, bool $result, string $value): bool
    {
        return false;
    }
}
