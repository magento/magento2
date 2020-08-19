<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Test\Unit\Cookie;

use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test PublicCookieMetadata
 *
 */
class PublicCookieMetadataTest extends TestCase
{
    /** @var PublicCookieMetadata */
    private $publicCookieMetadata;
    /** @var  ObjectManager */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->publicCookieMetadata = $this->objectManager->getObject(
            PublicCookieMetadata::class
        );
    }

    /**
     * @param StringUtils $setMethodName
     * @param StringUtils $getMethodName
     * @param StringUtils $expectedValue
     * @dataProvider getMethodData
     */
    public function testGetters($setMethodName, $getMethodName, $expectedValue)
    {
        $this->publicCookieMetadata->$setMethodName($expectedValue);
        $this->assertSame($expectedValue, $this->publicCookieMetadata->$getMethodName());
    }

    /**
     * @return array
     */
    public function getMethodData()
    {
        return [
            "getDomain" => ["setDomain", 'getDomain', "example.com"],
            "getPath" => ["setPath", 'getPath', "path"],
            "getDuration" => ["setDuration", 'getDuration', 125],
            "getHttpOnly" => ["setHttpOnly", 'getHttpOnly', true],
            "getSecure" => ["setSecure", 'getSecure', true],
            "getDurationOneYear" => ["setDurationOneYear", 'getDuration', (3600*24*365)],
            "getSameSite" => ["setSameSite", 'getSameSite', 'Lax']
        ];
    }

    /**
     * @return array
     */
    public function toArrayDataProvider(): array
    {
        return [
            [
                [
                    PublicCookieMetadata::KEY_SECURE => false,
                    PublicCookieMetadata::KEY_DOMAIN => 'domain',
                    PublicCookieMetadata::KEY_PATH => 'path',
                ],
                [
                    PublicCookieMetadata::KEY_SECURE => false,
                    PublicCookieMetadata::KEY_DOMAIN => 'domain',
                    PublicCookieMetadata::KEY_PATH => 'path',
                    PublicCookieMetadata::KEY_SAME_SITE => 'Lax',
                ],
            ]
        ];
    }

    /**
     * Test To Array
     *
     * @param array $metadata
     * @param array $expected
     * @dataProvider toArrayDataProvider
     * @return void
     */
    public function testToArray(array $metadata, array $expected): void
    {
        /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $object */
        $object = $this->objectManager->getObject(
            PublicCookieMetadata::class,
            [
                'metadata' => $metadata,
            ]
        );
        $this->assertEquals($expected, $object->__toArray());
    }

    /**
     * Test Set SameSite None With Insecure Cookies
     *
     * @return void
     */
    public function testSetSecureWithSameSiteNone(): void
    {
        /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $publicCookieMetadata */
        $publicCookieMetadata = $this->objectManager->getObject(
            PublicCookieMetadata::class
        );
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cookie must be secure in order to use the SameSite None directive.');
        $publicCookieMetadata->setSameSite('None');
        $publicCookieMetadata->setSecure(false);
    }
}
