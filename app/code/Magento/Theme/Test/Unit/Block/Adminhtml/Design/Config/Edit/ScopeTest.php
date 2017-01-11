<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\Scope;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scope
     */
    protected $block;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ScopeResolverPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolverPool;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->initContext();

        $this->scopeResolverPool = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new Scope(
            $this->context,
            $this->scopeResolverPool
        );
    }

    public function testGetScopeTitle()
    {
        $scope = 'websites';
        $scopeId = 1;
        $scopeTypeName = 'Website';

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $scopeObject = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
            ->getMockForAbstractClass();
        $scopeObject->expects($this->once())
            ->method('getScopeTypeName')
            ->willReturn($scopeTypeName);

        $scopeResolver = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
            ->getMockForAbstractClass();
        $scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($scopeId)
            ->willReturn($scopeObject);

        $this->scopeResolverPool->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willReturn($scopeResolver);

        $this->assertEquals(__('%1', $scopeTypeName), $this->block->getScopeTitle());
    }

    public function testGetScopeTitleDefault()
    {
        $scope = 'default';
        $scopeId = 0;
        $scopeTypeName = 'Default';

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $this->assertEquals($scopeTypeName, $this->block->getScopeTitle()->render());
    }

    protected function initContext()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
    }
}
