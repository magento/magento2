<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit;

use Magento\Framework\Acl\Builder;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_aclFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_aclMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_ruleLoader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_roleLoader;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceLoader;

    /**
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_aclMock = new \Magento\Framework\Acl();
        $this->_aclFactoryMock = $this->createMock(\Magento\Framework\AclFactory::class);
        $this->_aclFactoryMock->expects($this->any())->method('create')->willReturn($this->_aclMock);
        $this->_roleLoader = $this->createMock(\Magento\Framework\Acl\Loader\DefaultLoader::class);
        $this->_ruleLoader = $this->createMock(\Magento\Framework\Acl\Loader\DefaultLoader::class);
        $this->_resourceLoader = $this->createMock(\Magento\Framework\Acl\Loader\DefaultLoader::class);
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
     */
    public function testGetAclRethrowsException()
    {
        $this->expectException(\LogicException::class);

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
