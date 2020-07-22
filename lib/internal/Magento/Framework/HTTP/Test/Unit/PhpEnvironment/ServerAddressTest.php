<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\PhpEnvironment\ServerAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServerAddressTest extends TestCase
{
    /**
     * @var ServerAddress
     */
    protected $_serverAddress;

    /**
     * @var MockObject|Http
     */
    protected $_request;

    protected function setUp(): void
    {
        $this->_request = $this->getMockBuilder(
            Http::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getServer']
            )->getMock();

        $objectManager = new ObjectManager($this);
        $this->_serverAddress = $objectManager->getObject(
            ServerAddress::class,
            ['httpRequest' => $this->_request]
        );
    }

    /**
     * @dataProvider getServerAddressProvider
     */
    public function testGetServerAddress($serverVar, $expected, $ipToLong)
    {
        $this->_request->expects(
            $this->atLeastOnce()
        )->method(
            'getServer'
        )->with(
            'SERVER_ADDR'
        )->willReturn(
            $serverVar
        );
        $this->assertEquals($expected, $this->_serverAddress->getServerAddress($ipToLong));
    }

    /**
     * @return array
     */
    public function getServerAddressProvider()
    {
        return [
            [null, false, false],
            ['192.168.0.1', '192.168.0.1', false],
            ['192.168.1.1', ip2long('192.168.1.1'), true]
        ];
    }
}
