<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit;

use Magento\Framework\Acl\Builder;

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

    protected function setUp()
    {
        $this->_aclMock = new \Magento\Framework\Acl();
        $this->_aclFactoryMock = $this->getMock(\Magento\Framework\AclFactory::class, [], [], '', false);
        $this->_aclFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_aclMock));
        $this->_roleLoader = $this->getMock(\Magento\Framework\Acl\Loader\DefaultLoader::class);
        $this->_ruleLoader = $this->getMock(\Magento\Framework\Acl\Loader\DefaultLoader::class);
        $this->_resourceLoader = $this->getMock(\Magento\Framework\Acl\Loader\DefaultLoader::class);
        $this->_model = new \Magento\Framework\Acl\Builder(
            $this->_aclFactoryMock,
            $this->_roleLoader,
            $this->_resourceLoader,
            $this->_ruleLoader
        );
    }

    public function testGetAclUsesLoadersProvidedInConfigurationToPopulateAclIfCacheIsEmpty()
    {
        $this->_ruleLoader->expects($this->once())->method('populateAcl')->with($this->equalTo($this->_aclMock));

        $this->_roleLoader->expects($this->once())->method('populateAcl')->with($this->equalTo($this->_aclMock));

        $this->_resourceLoader->expects($this->once())->method('populateAcl')->with($this->equalTo($this->_aclMock));

        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
    }

    public function testGetAclReturnsAclStoredInCache()
    {
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetAclRethrowsException()
    {
        $this->_aclFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->throwException(new \InvalidArgumentException())
        );
        $this->_model->getAcl();
    }

    public function testResetRuntimeAcl()
    {
        $this->assertInstanceOf(Builder::class, $this->_model->resetRuntimeAcl());
    }
}
