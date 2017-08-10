<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * TODO: more clear description
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
    public function validate(SourceInterface $source);
}
