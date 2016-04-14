<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /** @var \Magento\Framework\Json\EncoderInterface | \PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var \Magento\Framework\Json\DecoderInterface | \PHPUnit_Framework_MockObject_MockObject  */
    protected $jsonDecoderMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->jsonEncoderMock = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonDecoderMock = $this->getMockBuilder('Magento\Framework\Json\DecoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $objectManager->getObject(
            'Magento\Framework\Json\Helper\Data',
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'jsonDecoder' => $this->jsonDecoderMock,
            ]
        );
    }

    public function testJsonEncode()
    {
        $expected = '"valueToEncode"';
        $valueToEncode = 'valueToEncode';
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->helper->jsonEncode($valueToEncode));
    }

    public function testJsonDecode()
    {
        $expected = '"valueToDecode"';
        $valueToDecode = 'valueToDecode';
        $this->jsonDecoderMock->expects($this->once())
            ->method('decode')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->helper->jsonDecode($valueToDecode));
    }
}
