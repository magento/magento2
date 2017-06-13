<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\Composite;
use Magento\Ui\Config\ConverterInterface;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    public function setUp()
    {
        $this->converter = $this->getMockBuilder(ConverterInterface::class)->getMockForAbstractClass();
    }

    public function testConvert()
    {
        $expectedResult = ['converted config'];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $element = $dom->createElement('name');
        $dom->appendChild($element);

        $composite = new Composite(['key' => $this->converter], 'type');
        $this->converter->expects($this->once())
            ->method('convert')
            ->with($element)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $composite->convert($element, ['type' => 'key']));
    }

    /**
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument converter named 'missedKey' has not been defined.
     */
    public function testConvertWithMissedConverter()
    {
        $element = new \DOMElement('name');
        $composite = new Composite(['key' => $this->converter], 'type');
        $composite->convert($element, ['type' => 'missedKey']);
    }

    /**
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Converter named 'key' is expected to be an argument converter instance.
     */
    public function testConvertWithInvalidConverter()
    {
        $element = new \DOMElement('name');
        $std = new \stdClass();
        $composite = new Composite(['key' => $std], 'type');
        $composite->convert($element, ['type' => 'key']);
    }
}
