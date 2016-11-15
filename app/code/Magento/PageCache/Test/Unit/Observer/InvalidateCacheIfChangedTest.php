<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Observer;

/**
 * Class InvalidateCacheIfChangedTest
 * @deprecated 
 */
class InvalidateCacheIfChangedTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Observer\InvalidateCacheIfChanged */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\TypeListInterface */
    protected $typeListMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer */
    protected $observerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject\IdentityInterface */
    protected $objectMock;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['getType', 'isEnabled'],
            [],
            '',
            false
        );
        $this->typeListMock = $this->getMock('Magento\Framework\App\Cache\TypeList', [], [], '', false);

        $this->model = new \Magento\PageCache\Observer\InvalidateCacheIfChanged(
            $this->configMock,
            $this->typeListMock
        );

        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);
        $this->objectMock = $this->getMockForAbstractClass(
            'Magento\Framework\DataObject\IdentityInterface',
            [],
            '',
            false
        );
        $eventMock->expects($this->any())->method('getObject')->willReturn($this->objectMock);
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
    }

    /**
     * @dataProvider invalidateCacheDataProvider
     * @param bool $cacheState
     */
    public function testExecuteChanged($cacheState)
    {
        $this->configMock->expects($this->once())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($cacheState) {
            $this->typeListMock->expects($this->once())->method('invalidate')->with($this->equalTo('full_page'));
            $this->objectMock->expects($this->once())->method('getIdentities')->will($this->returnValue(['tag_1']));
        }
        $this->model->execute($this->observerMock);
    }

    /**
     * @dataProvider invalidateCacheDataProvider
     * @param bool $cacheState
     */
    public function testExecuteNoChanged($cacheState)
    {
        $this->configMock->expects($this->once())->method('isEnabled')->will($this->returnValue($cacheState));
        $this->typeListMock->expects($this->never())->method('invalidate');

        if ($cacheState) {
            $this->objectMock->expects($this->once())->method('getIdentities')->will($this->returnValue([]));
        }
        $this->model->execute($this->observerMock);
    }

    public function invalidateCacheDataProvider()
    {
        return [[true], [false]];
    }
}
