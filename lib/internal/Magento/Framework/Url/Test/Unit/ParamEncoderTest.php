<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\Url\ParamEncoder;
use Magento\Framework\ZendEscaper;

/**
 * Test for \Magento\Framework\Url\ParamEncoder.
 */
class ParamEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParamEncoder
     */
    private $paramEncoder;

    /**
     * @var ZendEscaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    protected function setUp()
    {
        $this->escaperMock = $this->getMockBuilder(ZendEscaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paramEncoder = new ParamEncoder($this->escaperMock);
    }

    public function testEncode()
    {
        $stringToEncode = 'http://stringToEncode.com?with_params=true';
        $encodedString = 'http%3A%2F%2FstringToEncode.com%3Fwith_params%3Dtrue';
        $this->escaperMock
            ->expects($this->once())
            ->method('escapeUrl')
            ->with($stringToEncode)
            ->willReturn($encodedString);

        $this->assertEquals($encodedString, $this->paramEncoder->encode($stringToEncode));
    }
}
