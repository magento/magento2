<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Validator\HTML;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates "style" attribute.
 */
class StyleAttributeValidator implements AttributeValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validate(string $tag, string $attributeName, string $value): void
    {
        if ($attributeName !== 'style' || !$value) {
            return;
        }

        if (preg_match('/([^\-]position\s*?\:\s*?[^i\s][^n\s]\w)|(opacity)|(z-index)/ims', " $value")) {
            throw new ValidationException(__('HTML attribute "style" contains restricted styles'));
        }
    }
}
