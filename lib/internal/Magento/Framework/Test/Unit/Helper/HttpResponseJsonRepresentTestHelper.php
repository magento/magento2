<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\App\Response\Http;

/**
 * Test helper that overrides representJson behaviour for unit testing.
 */
class HttpResponseJsonRepresentTestHelper extends Http
{
    /**
     * Override parent constructor; not needed for unit tests.
     */
    public function __construct()
    {
    }

    /**
     * @param string $jsonResult
     * @return string
     */
    public function representJson($jsonResult)
    {
        if ($jsonResult === 'json encoded') {
            return 'json represented';
        }
        return $jsonResult;
    }
}
