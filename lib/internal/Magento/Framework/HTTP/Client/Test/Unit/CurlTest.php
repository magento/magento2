<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Client\Test\Unit;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\Socket;
use Magento\Framework\HTTP\ClientInterface;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    /** Methods required on all ClientInterface implementations (excludes CONNECT — conflicts with Socket's TCP connect). */
    private const INTERFACE_METHODS = ['put', 'delete', 'patch', 'options', 'head', 'trace'];
    /** All methods available on Curl (superset of interface, includes CONNECT). */
    private const CURL_METHODS = ['put', 'delete', 'patch', 'options', 'head', 'trace', 'connect'];

    /**
     * @return void
     */
    public function testPublicHttpMethodsExistOnCurl(): void
    {
        foreach (self::CURL_METHODS as $method) {
            $this->assertTrue(
                method_exists(Curl::class, $method),
                sprintf('Method %s must exist on Curl class', $method)
            );
        }
    }

    /**
     * @return void
     */
    public function testPublicHttpMethodsExistOnSocket(): void
    {
        foreach (self::INTERFACE_METHODS as $method) {
            $this->assertTrue(
                method_exists(Socket::class, $method),
                sprintf('Method %s must exist on Socket class', $method)
            );
        }
    }

    /**
     * @return void
     */
    public function testPublicHttpMethodsExistOnClientInterface(): void
    {
        foreach (self::INTERFACE_METHODS as $method) {
            $this->assertTrue(
                method_exists(ClientInterface::class, $method),
                sprintf('Method %s must exist on ClientInterface', $method)
            );
        }
    }
}
