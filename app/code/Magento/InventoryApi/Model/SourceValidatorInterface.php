<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Responsible for Source validation
 * Extension point for base validation
 *
 * @api
 */
interface SourceValidatorInterface
{
    /**
     * @param SourceInterface $source
     * @return ValidationResult
     */
    public function validate(SourceInterface $source): ValidationResult;
}
