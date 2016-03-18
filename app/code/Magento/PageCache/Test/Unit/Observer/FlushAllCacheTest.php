<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Test\Unit\Observer;

class FlushAllCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Observer\FlushAllCache */
    private $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    private $_configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\PageCache\Cache */
    private $_cacheMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer */
    private $observerMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Cache\Type */
    private $fullPageCacheMock;

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
        $this->_cacheMock = $this->getMock('Magento\Framework\App\PageCache\Cache', ['clean'], [], '', false);
        $this->fullPageCacheMock = $this->getMock('\Magento\PageCache\Model\Cache\Type', ['clean'], [], '', false);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer');

        $this->_model = new \Magento\PageCache\Observer\FlushAllCache(
            $this->_configMock,
            $this->_cacheMock
        );

        $reflection = new \ReflectionClass('\Magento\PageCache\Observer\FlushAllCache');
        $reflectionProperty = $reflection->getProperty('fullPageCache');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_model, $this->fullPageCacheMock);
    }

    /**
     * Test case for flushing all the cache
     */
    public function testExecute()
    {
        $this->_configMock->expects(
            $this->once()
        )->method(
                'getType'
            )->will(
                $this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN)
            );

        $this->fullPageCacheMock->expects($this->once())->method('clean');
        $this->_model->execute($this->observerMock);
    }
}
