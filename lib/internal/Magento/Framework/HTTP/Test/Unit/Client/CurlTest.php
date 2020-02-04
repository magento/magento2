<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\Client;

use Magento\Framework\HTTP\Client\Curl;
use PHPUnit\Framework\TestCase;

/**
 * Test HTTP client based on cUrl.
 */
class CurlTest extends TestCase
{
    /**
     * Check that HTTP client can be used only for HTTP.
     *
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp  /Protocol .?telnet.? not supported or disabled in libcurl/
     */
    public function testInvalidProtocol()
    {
        $client = new Curl();
        $client->get('telnet://127.0.0.1/test');
    }
}
