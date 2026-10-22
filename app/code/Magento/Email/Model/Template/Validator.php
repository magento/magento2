<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Template;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates email template content
 */
class Validator
{
    /**
     * Regular expressions for HTML attribute value containing a template directive
     *
     * @var string
     */
    private const ATTRIBUTE_VALUE_EXPRESSION =
        '/\b[a-zA-Z_:][-a-zA-Z0-9_:.]*\s*=\s*' .
        '(?|"([^"]*\{\{[^}]*\}\}[^"]*)"' .
        '|\'([^\']*\{\{[^}]*\}\}[^\']*)\'' .
        '|((?:\{\{[^}]*\}\}|[^\s"\'<>`])+))/iu';

    /**
     * Regular expressions for CSS url argument containing a template directive
     *
     * @var string
     */
    private const CSS_URL_VALUE_EXPRESSION =
        '/\burl\(\s*(?|"([^"]*\{\{[^}]*\}\}[^"]*)"' .
        '|\'([^\']*\{\{[^}]*\}\}[^\']*)\'' .
        '|((?:\{\{[^}]*\}\}|[^\s"\')])+))\s*\)/iu';

    /**
     * Validate template content
     *
     * @param string $content
     *
     * @throws ValidationException
     */
    public function validate(string $content): void
    {
        foreach ([self::ATTRIBUTE_VALUE_EXPRESSION, self::CSS_URL_VALUE_EXPRESSION] as $expression) {
            $matchCount = preg_match_all($expression, $content, $matches);
            if ($matchCount === false) {
                throw new ValidationException(
                    __('Template content validation failed. Please review template syntax.')
                );
            }
            foreach ($matches[1] as $value) {
                if ($this->isForbiddenUrlValue($value)) {
                    throw new ValidationException(
                        __('Template content validation failed. Please review template syntax.')
                    );
                }
            }
        }
    }

    /**
     * Check whether a URL-bearing value resolves to an absolute external target
     *
     * @param string $value
     * @return bool
     */
    private function isForbiddenUrlValue(string $value): bool
    {
        $normalized = $this->normalizeUrlValue($value);
        if (strpos($normalized, '{{') === false) {
            return false;
        }

        return preg_match('#^https?:/#i', $normalized) === 1
            || strncmp($normalized, '//', 2) === 0;
    }

    /**
     * Normalize a URL-bearing value
     *
     * @param string $value
     * @return string
     */
    private function normalizeUrlValue(string $value): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_ireplace(['%2f', '%5c'], ['/', '\\'], $value);
        $value = str_replace('\\', '/', $value);

        return preg_replace('/[\x00-\x20]+/', '', $value) ?? $value;
    }
}
