<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleLoader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_roleLoader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceLoader;

    /**
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclCacheMock;

    protected function setUp()
    {
        $this->_aclMock = new \Magento\Framework\Acl();
        $this->_aclCacheMock = $this->getMock('Magento\Framework\Acl\CacheInterface');
        $this->_aclFactoryMock = $this->getMock('Magento\Framework\AclFactory', [], [], '', false);
        $this->_aclFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_aclMock));
        $this->_roleLoader = $this->getMock('Magento\Framework\Acl\Loader\DefaultLoader');
        $this->_ruleLoader = $this->getMock('Magento\Framework\Acl\Loader\DefaultLoader');
        $this->_resourceLoader = $this->getMock('Magento\Framework\Acl\Loader\DefaultLoader');
        $this->_model = new \Magento\Framework\Acl\Builder(
            $this->_aclFactoryMock,
            $this->_aclCacheMock,
            $this->_roleLoader,
            $this->_resourceLoader,
            $this->_ruleLoader
        );
    }

    public function testGetAclUsesLoadersProvidedInConfigurationToPopulateAclIfCacheIsEmpty()
    {
        $this->_aclCacheMock->expects($this->at(1))->method('has')->will($this->returnValue(false));
        $this->_aclCacheMock->expects($this->at(2))->method('has')->will($this->returnValue(true));
        $this->_aclCacheMock->expects($this->once())->method('get')->will($this->returnValue($this->_aclMock));
        $this->_aclCacheMock->expects($this->exactly(1))->method('save')->with($this->_aclMock);
        $this->_ruleLoader->expects($this->once())->method('populateAcl')->with($this->equalTo($this->_aclMock));

        $this->_roleLoader->expects($this->once())->method('populateAcl')->with($this->equalTo($this->_aclMock));

        $this->_resourceLoader->expects($this->once())->method('populateAcl')->with($this->equalTo($this->_aclMock));

        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
    }

    public function testGetAclReturnsAclStoredInCache()
    {
        $this->_aclCacheMock->expects($this->exactly(2))->method('has')->will($this->returnValue(true));
        $this->_aclCacheMock->expects($this->exactly(2))->method('get')->will($this->returnValue($this->_aclMock));
        $this->_aclCacheMock->expects($this->never())->method('save');
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetAclRethrowsException()
    {
        $this->_aclCacheMock->expects(
            $this->once()
        )->method(
            'has'
        )->will(
            $this->throwException(new \InvalidArgumentException())
        );
        $this->_model->getAcl();
    }
}
