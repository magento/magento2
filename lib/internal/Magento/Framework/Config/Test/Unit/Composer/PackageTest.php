<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Config\Test\Unit\Composer;

use \Magento\Framework\Config\Composer\Package;

class PackageTest extends \PHPUnit_Framework_TestCase
{
    const SAMPLE_DATA =
        '{"foo":"1","bar":"2","baz":["3","4"],"nested":{"one":"5","two":"6",
        "magento/theme-adminhtml-backend":7, "magento/theme-frontend-luma":8}}';

    /**
     * @var \StdClass
     */
    private $sampleJson;

    /**
     * @var Package
     */
    private $object;

    protected function setUp()
    {
        $this->sampleJson = json_decode(self::SAMPLE_DATA);
        $this->object = new Package($this->sampleJson);
    }

    public function testGetJson()
    {
        $this->assertInstanceOf('\StdClass', $this->object->getJson(false));
        $this->assertEquals($this->sampleJson, $this->object->getJson(false));
        $this->assertSame($this->sampleJson, $this->object->getJson(false));
        $this->assertEquals(
            json_encode($this->sampleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
            $this->object->getJson(true, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function testGet()
    {
        $this->assertSame('1', $this->object->get('foo'));
        $this->assertSame(['3', '4'], $this->object->get('baz'));
        $nested = $this->object->get('nested');
        $this->assertInstanceOf('\StdClass', $nested);
        $this->assertObjectHasAttribute('one', $nested);
        $this->assertEquals('5', $nested->one);
        $this->assertEquals('5', $this->object->get('nested->one'));
        $this->assertObjectHasAttribute('two', $nested);
        $this->assertEquals('6', $nested->two);
        $this->assertEquals('6', $this->object->get('nested->two'));
        $this->assertEquals(
            ['magento/theme-adminhtml-backend' => 7, 'magento/theme-frontend-luma' => 8],
            (array)$this->object->get('nested', '/^magento\/theme/')
        );
    }
}
