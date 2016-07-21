<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Xml;

use Magento\Framework\MessageQueue\Publisher\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->converter = new Converter(new BooleanUtils());
    }

    public function testConvert()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_publisher';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $result = $this->converter->convert($dom);

        $expectedData = include($fixtureDir . '/valid.php');
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Connection name is missing
     */
    public function testConvertWithException()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_publisher';
        $xmlFile = $fixtureDir . '/invalid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $this->converter->convert($dom);
    }
}
