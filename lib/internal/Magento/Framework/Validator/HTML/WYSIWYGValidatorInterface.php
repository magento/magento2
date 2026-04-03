<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\HTML;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates user HTML.
 *
 * @api
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
