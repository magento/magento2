<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Observer;

class InvalidateCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\PageCache\Observer\InvalidateCache */
    protected $_model;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var  \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\Cache\TypeListInterface */
    protected $_typeListMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject|
     */
    protected $observerMock;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $this->_configMock = $this->createPartialMock(\Magento\PageCache\Model\Config::class, ['getType', 'isEnabled']);
        $this->_typeListMock = $this->createMock(\Magento\Framework\App\Cache\TypeList::class);

        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);

        $this->_model = new \Magento\PageCache\Observer\InvalidateCache(
            $this->_configMock,
            $this->_typeListMock
        );
    }

    /**
     * @dataProvider invalidateCacheDataProvider
     * @param bool $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->_configMock->expects($this->once())->method('isEnabled')->willReturn($cacheState);

        if ($cacheState) {
            $this->_typeListMock->expects($this->once())->method('invalidate')->with($this->equalTo('full_page'));
        }

        $this->_model->execute($this->observerMock);
    }

    /**
     * @return array
     */
    public function invalidateCacheDataProvider()
    {
        return [[true], [false]];
    }
}
