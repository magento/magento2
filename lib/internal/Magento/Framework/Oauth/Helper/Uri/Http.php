<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper\Uri;

use Laminas\Uri\Http as LaminasHttp;

class Http extends LaminasHttp
{
    /**
     * @inheritDoc
     */
    protected static function normalizePath($path): string
    {
        return self::encodePath(
            self::decodeUrlEncodedChars(
                self::removePathDotSegments($path),
                '/[' . self::CHAR_UNRESERVED . ']/'
            )
        );
    }
}
