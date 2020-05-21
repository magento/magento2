<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\Scope;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
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
     * @var ScopeResolverPool|MockObject
     */
    protected $scopeResolverPool;

    /**
     * @var Http|MockObject
     */
    protected $request;

    protected function setUp(): void
    {
        $this->initContext();

        $this->scopeResolverPool = $this->getMockBuilder(ScopeResolverPool::class)
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

        $scopeObject = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $scopeObject->expects($this->once())
            ->method('getScopeTypeName')
            ->willReturn($scopeTypeName);

        $scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
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
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
    }
}
