<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Xml;

use Magento\Framework\MessageQueue\Topology\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Data\Argument\InterpreterInterface;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $interpreter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->interpreter = $this->getMock(InterpreterInterface::class);
        $this->converter = new Converter(new BooleanUtils(), $this->interpreter);

    }

    public function testConvert()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_topology';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);

        $this->interpreter->expects($this->any())->method('evaluate')->willReturn(10);
        $result = $this->converter->convert($dom);

        $expectedData = include($fixtureDir . '/valid.php');
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }
}
