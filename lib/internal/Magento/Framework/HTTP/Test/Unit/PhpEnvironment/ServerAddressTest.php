<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

class ServerAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress
     */
    protected $_serverAddress;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $_request;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(
            \Magento\Framework\App\Request\Http::class
        )->disableOriginalConstructor()->setMethods(
            ['getServer']
        )->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_serverAddress = $objectManager->getObject(
            \Magento\Framework\HTTP\PhpEnvironment\ServerAddress::class,
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
        )->will(
            $this->returnValue($serverVar)
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
