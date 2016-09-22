<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Config\Test\Unit;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\ReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMockBuilder(\Magento\Framework\Config\ReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\Config\CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetConfigNotCached()
    {
        $data = ['a' => 'b'];
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->readerMock->expects($this->once())
            ->method('read')
            ->willReturn($data);

        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
        $this->assertEquals(null, $config->get('a/b'));
        $this->assertEquals(33, $config->get('a/b', 33));
    }

    public function testGetConfigCached()
    {
        $data = ['a' => 'b'];
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('{"a":"b"}');
        $this->readerMock->expects($this->never())
            ->method('read');

        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId
        );

        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
    }

    public function testReset()
    {
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(\Zend_Json::encode([]));
        $this->cacheMock->expects($this->once())
            ->method('remove')
            ->with($cacheId);

        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId
        );

        $config->reset();
    }
}
