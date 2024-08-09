<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

/**
 * Class GlobalForbiddenPatterns
 * Provides a set of forbidden patterns used for validation across the application.
 */
class GlobalForbiddenPatterns
{
    /**
     * Returns an array of forbidden patterns.
     *
     * @return string[]
     */
    public static function getPatterns(): array
    {
        return [
            '/{{.*}}/',
            '/<\?=/',
            '/<\?php/',
            '/base64_decode/',
            '/shell_exec/',
            '/eval\(/',
            '/\${IFS%/',
            '/\bcurl\b/',
        ];
    }
}
