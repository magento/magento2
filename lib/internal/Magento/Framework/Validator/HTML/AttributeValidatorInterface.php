<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Validator\HTML;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates HTML attributes content.
 */
interface AttributeValidatorInterface
{
    /**
     * Validate attribute.
     *
     * @param string $tag
     * @param string $attributeName
     * @param string $value
     * @return void
     * @throws ValidationException
     */
    public function validate(string $tag, string $attributeName, string $value): void;
}
