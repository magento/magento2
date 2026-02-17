<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;

/**
 * Wishlist message validator with strict content rules.
 */
class MessageValidator extends AbstractValidator
{
    /**
     * Patterns that indicate template injection or code execution attempts.
     */
    private const FORBIDDEN_PATTERNS = [
        // Template directives
        '/\{\{.*?\}\}/s',
        '/\{%.*?%\}/s',
    
        // Server-side code execution
        '/<\?/i',

        // Template filter/processor access (Magento-specific)
        '/\bthis\s*\.\s*get\w+/i',
        '/TemplateFilter|FilterCallback/i',
    ];

    /**
     * Validates the message against allowed patterns.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return true;
        }

        // Decode URL encoding to catch obfuscation
        $decoded = urldecode($value);

        // Remove newlines/carriage returns that might be used for obfuscation
        $normalized = preg_replace('/[\r\n\t]+/', ' ', $decoded);

        // Check for suspicious patterns in both decoded and normalized versions
        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (preg_match($pattern, $decoded) || preg_match($pattern, $normalized)) {
                $this->_addMessages([
                    'Invalid content detected in message. Code and system commands are not allowed.'
                ]);
                return false;
            }
        }

        return true;
    }
}
