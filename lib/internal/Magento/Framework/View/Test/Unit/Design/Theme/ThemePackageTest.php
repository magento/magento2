<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\View\Design\Theme\ThemePackage;
use PHPUnit\Framework\TestCase;

class ThemePackageTest extends TestCase
{
    /**
     * @param string $key
     *
     * @dataProvider constructBadKeyDataProvider
     */
    public function testConstructBadKey($key)
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(
            'Theme\'s key does not correspond to required format: \'<area>/<vendor>/<name>\''
        );
        new ThemePackage($key, 'path');
    }

    /**
     * @return array
     */
    public static function constructBadKeyDataProvider()
    {
        return [
            [''],
            ['one'],
            ['two/parts'],
            ['four/parts/four/parts'],
        ];
    }

    public function testGetters()
    {
        $key = 'area/Vendor/name';
        $path = 'path';
        $object = new ThemePackage($key, $path);
        $this->assertSame('area', $object->getArea());
        $this->assertSame('Vendor', $object->getVendor());
        $this->assertSame('name', $object->getName());
        $this->assertSame($key, $object->getKey());
        $this->assertSame($path, $object->getPath());
    }
}
