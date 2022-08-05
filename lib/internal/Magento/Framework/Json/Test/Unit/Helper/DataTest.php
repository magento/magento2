<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Json\Test\Unit\Helper;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /** @var EncoderInterface|MockObject */
    protected $jsonEncoderMock;

    /** @var DecoderInterface|MockObject  */
    protected $jsonDecoderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->jsonEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->jsonDecoderMock = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helper = $objectManager->getObject(
            Data::class,
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
