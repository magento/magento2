<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Http;

use Magento\Payment\Gateway\Http\Transfer;
use PHPUnit\Framework\TestCase;

class TransferTest extends TestCase
{
    public function testIO()
    {
        $clientConfig = ['config'];
        $headers = ['Header'];
        $body = ['data', 'data2'];
        $auth = ['username', 'password'];
        $method = 'POST';
        $uri = 'https://gateway.com';
        $encode = false;

        $transfer = new Transfer(
            $clientConfig,
            $headers,
            $body,
            $auth,
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
