<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\Country\Postcode\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->readerMock = $this->createMock(\Magento\Directory\Model\Country\Postcode\Config\Reader::class);
        $this->cacheMock = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(json_encode($expected));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expected);
        $configData = new \Magento\Directory\Model\Country\Postcode\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            'country_postcodes',
            $this->serializerMock
        );
        $this->assertEquals($expected, $configData->get());
    }
}
