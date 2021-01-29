<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use \Magento\Framework\Url\Decoder;
use \Magento\Framework\Url\Encoder;

class DecoderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Framework\Url\Encoder::encode
     * @covers \Magento\Framework\Url\Decoder::decode
     */
    public function testDecode()
    {
        $urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        /** @var $urlBuilderMock \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
        $decoder = new Decoder($urlBuilderMock);
        $encoder = new Encoder();

        $data = uniqid();
        $result = $encoder->encode($data);
        $urlBuilderMock->expects($this->once())
            ->method('sessionUrlVar')
            ->with($this->equalTo($data))
            ->willReturn($result);
        $this->assertStringNotContainsString('&', $result);
        $this->assertStringNotContainsString('%', $result);
        $this->assertStringNotContainsString('+', $result);
        $this->assertStringNotContainsString('=', $result);
        $this->assertEquals($result, $decoder->decode($result));
    }
}
