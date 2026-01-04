<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Client\Tests;

use Magento\Framework\HTTP\Client\Curl;
use PHPUnit\Framework\TestCase;

class CurlMethodsTest extends TestCase
{
    public function testPublicMethodsExist()
    {
        $methods = [
            'put',
            'delete',
            'patch',
            'options',
            'head',
            'trace',
            'connect'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists(Curl::class, $method), "Method $method should exist on Curl class");
        }
    }
}
