<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

class RemoteAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(
            \Magento\Framework\App\Request\Http::class
        )->disableOriginalConstructor()->setMethods(
            ['getServer']
        )->getMock();

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @dataProvider getRemoteAddressProvider
     */
    public function testGetRemoteAddress($alternativeHeaders, $serverValueMap, $expected, $ipToLong)
    {
        $remoteAddress = $this->_objectManager->getObject(
            \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress::class,
            ['httpRequest' => $this->_request, 'alternativeHeaders' => $alternativeHeaders]
        );
        $this->_request->expects($this->any())->method('getServer')->will($this->returnValueMap($serverValueMap));
        $this->assertEquals($expected, $remoteAddress->getRemoteAddress($ipToLong));
    }

    /**
     * @return array
     */
    public function getRemoteAddressProvider()
    {
        return [
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [['REMOTE_ADDR', null, null]],
                'expected' => false,
                'ipToLong' => false,
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [['REMOTE_ADDR', null, '192.168.0.1']],
                'expected' => '192.168.0.1',
                'ipToLong' => false
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [['REMOTE_ADDR', null, '192.168.1.1']],
                'expected' => ip2long('192.168.1.1'),
                'ipToLong' => true
            ],
            [
                'alternativeHeaders' => ['TEST_HEADER'],
                'serverValueMap' => [
                    ['REMOTE_ADDR', null, '192.168.1.1'],
                    ['TEST_HEADER', null, '192.168.0.1'],
                    ['TEST_HEADER', false, '192.168.0.1'],
                ],
                'expected' => '192.168.0.1',
                'ipToLong' => false
            ],
            [
                'alternativeHeaders' => ['TEST_HEADER'],
                'serverValueMap' => [
                    ['REMOTE_ADDR', null, '192.168.1.1'],
                    ['TEST_HEADER', null, '192.168.0.1'],
                    ['TEST_HEADER', false, '192.168.0.1'],
                ],
                'expected' => ip2long('192.168.0.1'),
                'ipToLong' => true
            ]
        ];
    }
}
