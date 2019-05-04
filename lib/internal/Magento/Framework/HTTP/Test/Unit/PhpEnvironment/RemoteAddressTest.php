<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RemoteAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\HttpRequest
     */
    protected $_request;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getServer'])
            ->getMock();

        $this->_objectManager = new ObjectManager($this);
    }

    /**
     * @param string[] $alternativeHeaders
     * @param array $serverValueMap
     * @param string|bool $expected
     * @param bool $ipToLong
     * @param string[]|null $trustedProxies
     * @return void
     * @dataProvider getRemoteAddressProvider
     */
    public function testGetRemoteAddress(
        array $alternativeHeaders,
        array $serverValueMap,
        $expected,
        bool $ipToLong,
        array $trustedProxies = null
    ): void {
        $remoteAddress = $this->_objectManager->getObject(
            RemoteAddress::class,
            [
                'httpRequest' => $this->_request,
                'alternativeHeaders' => $alternativeHeaders,
                'trustedProxies' => $trustedProxies,
            ]
        );
        $this->_request->expects($this->any())
            ->method('getServer')
            ->will($this->returnValueMap($serverValueMap));

        $this->assertEquals($expected, $remoteAddress->getRemoteAddress($ipToLong));
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getRemoteAddressProvider()
    {
        return [
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [['REMOTE_ADDR', null, null]],
                'expected' => false,
                'ipToLong' => false,
                'trustedProxies' => null,
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [['REMOTE_ADDR', null, '192.168.0.1']],
                'expected' => '192.168.0.1',
                'ipToLong' => false,
                'trustedProxies' => null,
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [['REMOTE_ADDR', null, '192.168.1.1']],
                'expected' => ip2long('192.168.1.1'),
                'ipToLong' => true,
                'trustedProxies' => null,
            ],
            [
                'alternativeHeaders' => ['TEST_HEADER'],
                'serverValueMap' => [
                    ['REMOTE_ADDR', null, '192.168.1.1'],
                    ['TEST_HEADER', null, '192.168.0.1'],
                    ['TEST_HEADER', false, '192.168.0.1'],
                ],
                'expected' => '192.168.0.1',
                'ipToLong' => false,
                'trustedProxies' => null,
            ],
            [
                'alternativeHeaders' => ['TEST_HEADER'],
                'serverValueMap' => [
                    ['REMOTE_ADDR', null, '192.168.1.1'],
                    ['TEST_HEADER', null, '192.168.0.1'],
                    ['TEST_HEADER', false, '192.168.0.1'],
                ],
                'expected' => ip2long('192.168.0.1'),
                'ipToLong' => true,
                'trustedProxies' => null,
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [
                    ['REMOTE_ADDR', null, 'NotValidIp'],
                ],
                'expected' => false,
                'ipToLong' => false,
                'trustedProxies' => ['127.0.0.1'],
            ],
            [
                'alternativeHeaders' => ['TEST_HEADER'],
                'serverValueMap' => [
                    ['TEST_HEADER', null, 'NotValid, 192.168.0.1'],
                    ['TEST_HEADER', false, 'NotValid, 192.168.0.1'],
                ],
                'expected' => '192.168.0.1',
                'ipToLong' => false,
                'trustedProxies' => ['127.0.0.1'],
            ],
            [
                'alternativeHeaders' => ['TEST_HEADER'],
                'serverValueMap' => [
                    ['TEST_HEADER', null, '192.168.0.2, 192.168.0.1'],
                    ['TEST_HEADER', false, '192.168.0.2, 192.168.0.1'],
                ],
                'expected' => '192.168.0.2',
                'ipToLong' => false,
                'trustedProxies' => null,
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [
                    [
                        'REMOTE_ADDR',
                        null,
                        '192.168.0.2, 192.168.0.1, 192.168.0.3',
                    ],
                    [
                        'REMOTE_ADDR',
                        false,
                        '192.168.0.2, 192.168.0.1, 192.168.0.3',
                    ],
                ],
                'expected' => '192.168.0.1',
                'ipToLong' => false,
                'trustedProxies' => ['192.168.0.3'],
            ],
            [
                'alternativeHeaders' => [],
                'serverValueMap' => [
                    [
                        'REMOTE_ADDR',
                        null,
                        '192.168.0.2, 192.168.0.1, 192.168.0.3',
                    ],
                    [
                        'REMOTE_ADDR',
                        false,
                        '192.168.0.2, 192.168.0.1, 192.168.0.3',
                    ],
                ],
                'expected' => '192.168.0.3',
                'ipToLong' => false,
                'trustedProxies' => [],
            ],
        ];
    }
}
