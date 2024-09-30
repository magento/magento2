<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Validator\HTML;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates user HTML.
 */
interface WYSIWYGValidatorInterface
{
    /**
     * Validate user HTML content.
     *
     * @param string $content
     * @throws ValidationException
     */
    public function validate(string $content): void;
}
