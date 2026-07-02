<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Client\Test\Unit;

use Magento\Framework\HTTP\Client\Curl;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    /**
     * @return void
     */
    public function testPublicHttpMethodsExist(): void
    {
        $methods = ['put', 'delete', 'patch', 'options', 'head', 'trace', 'connect'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(Curl::class, $method),
                sprintf('Method %s must exist on Curl class', $method)
            );
        }
    }
}
