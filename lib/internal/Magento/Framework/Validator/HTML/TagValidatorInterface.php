<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Validator\HTML;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates tag for user HTML content.
 */
interface TagValidatorInterface
{
    /**
     * Validate a tag.
     *
     * @param string $tag
     * @param string[] $attributes
     * @param string $value
     * @param WYSIWYGValidatorInterface $recursiveValidator
     * @return void
     * @throws ValidationException
     */
    public function validate(
        string $tag,
        array $attributes,
        string $value,
        WYSIWYGValidatorInterface $recursiveValidator
    ): void;
}
