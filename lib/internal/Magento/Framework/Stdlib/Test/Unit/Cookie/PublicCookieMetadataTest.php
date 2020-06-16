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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->publicCookieMetadata = $objectManager->getObject(
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
        ];
    }
}
