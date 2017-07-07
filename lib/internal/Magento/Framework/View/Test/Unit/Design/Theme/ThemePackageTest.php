<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\View\Design\Theme\ThemePackage;

class ThemePackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $key
     *
     * @dataProvider constructBadKeyDataProvider
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Theme's key does not correspond to required format: '<area>/<vendor>/<name>'
     */
    public function testConstructBadKey($key)
    {
        new ThemePackage($key, 'path');
    }

    /**
     * @return array
     */
    public function constructBadKeyDataProvider()
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
