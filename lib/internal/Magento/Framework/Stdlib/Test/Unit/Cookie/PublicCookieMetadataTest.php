<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Test\Unit\Cookie;

use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test PublicCookieMetadata
 *
 */
class PublicCookieMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var PublicCookieMetadata */
    private $publicCookieMetadata;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->publicCookieMetadata = $objectManager->getObject(
            'Magento\Framework\Stdlib\Cookie\PublicCookieMetadata'
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
