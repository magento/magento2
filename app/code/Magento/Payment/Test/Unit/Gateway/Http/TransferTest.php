<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Http;

use Magento\Payment\Gateway\Http\Transfer;

class TransferTest extends \PHPUnit\Framework\TestCase
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

        $this->assertSame($clientConfig, $transfer->getClientConfig());
        $this->assertSame($headers, $transfer->getHeaders());
        $this->assertSame($body, $transfer->getBody());
        $this->assertSame($method, $transfer->getMethod());
        $this->assertSame($uri, $transfer->getUri());
        $this->assertSame($encode, $transfer->shouldEncode());
    }
}
