<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see RemoteAddress
 */
class RemoteAddressTest extends TestCase
{
    /**
     * @var MockObject|HttpRequest
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getServer'])
            ->getMock();
    }

    /**
     * @param string[] $alternativeHeaders
     * @param array $serverValueMap
     * @param string|bool $expected
     * @param bool $ipToLong
     * @param string[]|null $trustedProxies
     *
     * @return void
     * @dataProvider getRemoteAddressProvider
     */
    public function testGetRemoteAddress(
        array $alternativeHeaders,
        array $serverValueMap,
        $expected,
        bool $ipToLong,
        ?array $trustedProxies = null
    ): void {
        $remoteAddress = new RemoteAddress(
            $this->requestMock,
            $alternativeHeaders,
            $trustedProxies
        );
        $this->requestMock->method('getServer')
            ->willReturnMap($serverValueMap);

        // Check twice to verify if internal variable is cached correctly
        $this->assertEquals($expected, $remoteAddress->getRemoteAddress($ipToLong));
        $this->assertEquals($expected, $remoteAddress->getRemoteAddress($ipToLong));
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getRemoteAddressProvider()
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
