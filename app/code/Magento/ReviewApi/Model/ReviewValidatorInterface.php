<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\ReviewApi\Api\Data\ReviewInterface;

/**
 * Interface for review validator
 *
 * @api
 */
interface ReviewValidatorInterface
{
    /**
     * Validate review instance
     *
     * Return array of errors if not valid
     *
     * @param ReviewInterface $review
     * @return ValidationResult
     */
    public function validate(ReviewInterface $review): ValidationResult;
}
