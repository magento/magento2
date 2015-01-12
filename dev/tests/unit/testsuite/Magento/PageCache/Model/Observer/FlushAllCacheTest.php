<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Observer;

class FlushAllCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Observer\FlushAllCache */
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

        $this->_model = new \Magento\PageCache\Model\Observer\FlushAllCache(
            $this->_configMock,
            $this->_cacheMock
        );
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

        $this->_cacheMock->expects($this->once())->method('clean');
        $this->_model->execute();
    }
}
