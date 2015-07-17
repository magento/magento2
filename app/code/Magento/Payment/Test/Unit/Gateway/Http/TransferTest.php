<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Http;

use Magento\Payment\Gateway\Http\Transfer;

class TransferTest extends \PHPUnit_Framework_TestCase
{
    public function testIO()
    {
        $clientConfig = ['config'];
        $headers = ['Header'];
        $body = ['data', 'data2'];
        $method = 'POST';
        $uri = 'https://gateway.com';
        $encode = false;

        $transfer = new Transfer(
            $clientConfig,
            $headers,
            $body,
            $method,
            $uri,
            $encode
        );

        static::assertSame($clientConfig, $transfer->getClientConfig());
        static::assertSame($headers, $transfer->getHeaders());
        static::assertSame($body, $transfer->getBody());
        static::assertSame($method, $transfer->getMethod());
        static::assertSame($uri, $transfer->getUri());
        static::assertSame($encode, $transfer->shouldEncode());
    }
}
