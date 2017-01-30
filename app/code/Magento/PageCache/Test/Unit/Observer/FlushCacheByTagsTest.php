<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Test\Unit\Observer;

class FlushCacheByTagsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Observer\FlushCacheByTags */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\PageCache\Cache */
    protected $_cacheMock;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->_configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['getType', 'isEnabled'],
            [],
            '',
            false
        );
        $this->_cacheMock = $this->getMock('Magento\Framework\App\PageCache\Cache', ['clean'], [], '', false);

        $this->_model = new \Magento\PageCache\Observer\FlushCacheByTags(
            $this->_configMock,
            $this->_cacheMock
        );
    }

    /**
     * Test case for cache invalidation
     *
     * @dataProvider flushCacheByTagsDataProvider
     * @param $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));
        $observerObject = $this->getMock('Magento\Framework\Event\Observer');
        $observedObject = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        if ($cacheState) {
            $tags = ['cache_1', 'cache_group'];
            $expectedTags = ['cache_1', 'cache_group', 'cache'];

            $eventMock = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);
            $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($observedObject));
            $observerObject->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
            $this->_configMock->expects(
                $this->once()
            )->method(
                    'getType'
                )->will(
                    $this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN)
                );
            $observedObject->expects($this->once())->method('getIdentities')->will($this->returnValue($tags));

            $this->_cacheMock->expects($this->once())->method('clean')->with($this->equalTo($expectedTags));
        }

        $this->_model->execute($observerObject);
    }

    public function flushCacheByTagsDataProvider()
    {
        return [
            'full_page cache type is enabled' => [true],
            'full_page cache type is disabled' => [false]
        ];
    }

    public function testExecuteWithEmptyTags()
    {
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $observerObject = $this->getMock('Magento\Framework\Event\Observer');
        $observedObject = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $tags = [];

        $eventMock = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);
        $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($observedObject));
        $observerObject->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN)
        );
        $observedObject->expects($this->once())->method('getIdentities')->will($this->returnValue($tags));

        $this->_cacheMock->expects($this->never())->method('clean');

        $this->_model->execute($observerObject);
    }
}
