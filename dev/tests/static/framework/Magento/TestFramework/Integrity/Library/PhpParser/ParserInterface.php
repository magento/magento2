<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Parser for each token type
 *
 */
interface ParserInterface
{
    /**
     * Parse specific token
     *
     * @param array|string $value
     * @param int $key
     */
    public function parse($value, $key);
}
