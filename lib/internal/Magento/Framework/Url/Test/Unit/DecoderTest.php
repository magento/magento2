<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\Url\Decoder;
use Magento\Framework\Url\Encoder;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    /**
     * @covers \Magento\Framework\Url\Encoder::encode
     * @covers \Magento\Framework\Url\Decoder::decode
     */
    public function testDecode()
    {
        $urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        /** @var UrlInterface|MockObject $urlBuilderMock */
        $decoder = new Decoder($urlBuilderMock);
        $encoder = new Encoder();

        $data = uniqid();
        $result = $encoder->encode($data);
        $urlBuilderMock->expects($this->once())
            ->method('sessionUrlVar')
            ->with($data)
            ->willReturn($result);
        $this->assertStringNotContainsString('&', $result);
        $this->assertStringNotContainsString('%', $result);
        $this->assertStringNotContainsString('+', $result);
        $this->assertStringNotContainsString('=', $result);
        $this->assertEquals($result, $decoder->decode($result));
    }
}
