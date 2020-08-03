<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Test\Unit\Cookie;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test SensitiveCookieMetaData
 *
 */
class SensitiveCookieMetadataTest extends TestCase
{
    /** @var  ObjectManager */
    private $objectManager;

    /** @var SensitiveCookieMetadata */
    private $sensitiveCookieMetadata;

    /** @var  Http|MockObject */
    private $requestMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sensitiveCookieMetadata = $this->objectManager->getObject(
            SensitiveCookieMetadata::class,
            [
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @param array $metadata
     * @param bool $httpOnly
     * @dataProvider constructorAndGetHttpOnlyTestDataProvider
     */
    public function testConstructorAndGetHttpOnly($metadata, $httpOnly)
    {
        /** @var \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata $object */
        $object = $this->objectManager->getObject(
            SensitiveCookieMetadata::class,
            [
                'request' => $this->requestMock,
                'metadata' => $metadata,

            ]
        );
        $this->assertEquals($httpOnly, $object->getHttpOnly());
        $this->assertEquals('domain', $object->getDomain());
        $this->assertEquals('path', $object->getPath());
    }

    /**
     * @return array
     */
    public function constructorAndGetHttpOnlyTestDataProvider()
    {
        return [
            'with httpOnly' => [
                [
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                false,
            ],
            'without httpOnly' => [
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                true,
            ],
        ];
    }

    /**
     * @param bool $isSecure
     * @param array $metadata
     * @param bool $expected
     * @param int $callNum
     * @dataProvider getSecureDataProvider
     */
    public function testGetSecure($isSecure, $metadata, $expected, $callNum = 1)
    {
        $this->requestMock->expects($this->exactly($callNum))
            ->method('isSecure')
            ->willReturn($isSecure);

        /** @var \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata $object */
        $object = $this->objectManager->getObject(
            SensitiveCookieMetadata::class,
            [
                'request' => $this->requestMock,
                'metadata' => $metadata,
            ]
        );
        $this->assertEquals($expected, $object->getSecure());
    }

    /**
     * @return array
     */
    public function getSecureDataProvider()
    {
        return [
            'with secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                false,
                0,
            ],
            'without secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                true,
            ],
            'without secure 2' => [
                false,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                false,
            ],
        ];
    }

    /**
     * @param bool $isSecure
     * @param array $metadata
     * @param bool $expected
     * @param int $callNum
     * @dataProvider toArrayDataProvider
     */
    public function testToArray($isSecure, $metadata, $expected, $callNum = 1)
    {
        $this->requestMock->expects($this->exactly($callNum))
            ->method('isSecure')
            ->willReturn($isSecure);

        /** @var \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata $object */
        $object = $this->objectManager->getObject(
            SensitiveCookieMetadata::class,
            [
                'request' => $this->requestMock,
                'metadata' => $metadata,
            ]
        );
        $this->assertEquals($expected, $object->__toArray());
    }

    /**
     * @return array
     */
    public function toArrayDataProvider()
    {
        return [
            'with secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => 1,
                ],
                0,
            ],
            'without secure' => [
                true,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    SensitiveCookieMetadata::KEY_SECURE => true,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => 1,
                ],
            ],
            'without secure 2' => [
                false,
                [
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    SensitiveCookieMetadata::KEY_SECURE => false,
                    SensitiveCookieMetadata::KEY_DOMAIN => 'domain',
                    SensitiveCookieMetadata::KEY_PATH => 'path',
                    SensitiveCookieMetadata::KEY_HTTP_ONLY => 1,
                ],
            ],
        ];
    }

    /**
     * @param StringUtils $setMethodName
     * @param StringUtils $getMethodName
     * @param StringUtils $expectedValue
     * @dataProvider getMethodData
     */
    public function testGetters($setMethodName, $getMethodName, $expectedValue)
    {
        $this->sensitiveCookieMetadata->$setMethodName($expectedValue);
        $this->assertSame($expectedValue, $this->sensitiveCookieMetadata->$getMethodName());
    }

    /**
     * @return array
     */
    public function getMethodData()
    {
        return [
            "getDomain" => ["setDomain", 'getDomain', "example.com"],
            "getPath" => ["setPath", 'getPath', "path"]
        ];
    }
}
