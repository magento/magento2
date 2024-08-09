<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

/**
 * Class GlobalForbiddenPatterns
 * Provides forbidden patterns for global validation.
 */
class GlobalForbiddenPatterns
{
    /**
     * Forbidden patterns for validation.
     *
     * @var string[]
     */
    public const PATTERNS = [
        '/{{.*}}/',
        '/<\?=/',
        '/<\?php/',
        '/base64_decode/',
        '/shell_exec/',
        '/eval\(/',
        '/\${IFS%/',
        '/\bcurl\b/',
    ];

    /**
     * Retrieve the forbidden patterns.
     *
     * @return string[]
     */
    public static function getPatterns(): array
    {
        return self::PATTERNS;
    }
}
