<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

use Magento\Webapi\Model\Laminas\Soap\Server;

/**
 * Test Class
 */
class MockServer extends Server
{
    public $mockSoapServer = null;
    public function getSoap()
    {
        $this->mockSoapServer = new MockSoapServer();
        return $this->mockSoapServer;
    }
}
