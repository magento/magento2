<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit\Client;

use Magento\Framework\HTTP\Client\Curl;

/**
 * Test HTTP client based on cUrl.
 */
class CurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check that HTTP client can be used only for HTTP, FTP.
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
