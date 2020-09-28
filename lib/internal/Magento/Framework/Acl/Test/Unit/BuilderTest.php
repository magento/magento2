<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit;

use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\Loader\DefaultLoader;
use Magento\Framework\AclFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_aclFactoryMock;

    /**
     * @var MockObject
     */
    protected $_aclMock;

    /**
     * @var MockObject
     */
    protected $_ruleLoader;

    /**
     * @var MockObject
     */
    protected $_roleLoader;

    /**
     * @var MockObject
     */
    protected $_resourceLoader;

    /**
     * @var Builder
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_aclMock = new Acl();
        $this->_aclFactoryMock = $this->createMock(AclFactory::class);
        $this->_aclFactoryMock->expects($this->any())->method('create')->willReturn($this->_aclMock);
        $this->_roleLoader = $this->createMock(DefaultLoader::class);
        $this->_ruleLoader = $this->createMock(DefaultLoader::class);
        $this->_resourceLoader = $this->createMock(DefaultLoader::class);
        $this->_model = new Builder(
            $this->_aclFactoryMock,
            $this->_roleLoader,
            $this->_resourceLoader,
            $this->_ruleLoader
        );
    }

    public function testGetAclUsesLoadersProvidedInConfigurationToPopulateAclIfCacheIsEmpty()
    {
        $this->_ruleLoader->expects($this->once())->method('populateAcl')->with($this->_aclMock);

        $this->_roleLoader->expects($this->once())->method('populateAcl')->with($this->_aclMock);

        $this->_resourceLoader->expects($this->once())->method('populateAcl')->with($this->_aclMock);

        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
    }

    public function testGetAclReturnsAclStoredInCache()
    {
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
        $this->assertEquals($this->_aclMock, $this->_model->getAcl());
    }

    public function testGetAclRethrowsException()
    {
        $this->expectException('LogicException');
        $this->_aclFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willThrowException(
            new \InvalidArgumentException()
        );
        $this->_model->getAcl();
    }

    public function testResetRuntimeAcl()
    {
        $this->assertInstanceOf(Builder::class, $this->_model->resetRuntimeAcl());
    }
}
