<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Observer;

class InvalidateCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Observer\InvalidateCache */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\TypeListInterface */
    protected $_typeListMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject|
     */
    protected $observerMock;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp()
    {
        $this->_configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['getType', 'isEnabled'],
            [],
            '',
            false
        );
        $this->_typeListMock = $this->getMock('Magento\Framework\App\Cache\TypeList', [], [], '', false);

        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer');

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
        $this->_configMock->expects($this->once())->method('isEnabled')->will($this->returnValue($cacheState));

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
