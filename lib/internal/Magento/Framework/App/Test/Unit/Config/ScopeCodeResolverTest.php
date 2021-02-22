<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;

class ScopeCodeResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeResolverPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeResolverPool;

    /**
     * @var ScopeResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeResolver;

    /**
     * @var ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scope;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    protected function setUp(): void
    {
        $this->scopeResolverPool = $this->getMockBuilder(ScopeResolverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeCodeResolver = new ScopeCodeResolver($this->scopeResolverPool);
    }

    public function testResolve()
    {
        $scopeType = 'website';
        $scopeCode = 'myWebsite';
        $scopeId = 4;
        $this->scopeResolverPool->expects($this->once())
            ->method('get')
            ->with($scopeType)
            ->willReturn($this->scopeResolver);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($scopeId)
            ->willReturn($this->scope);
        $this->scope->expects($this->once())
            ->method('getCode')
            ->willReturn($scopeCode);
        $this->assertEquals($scopeCode, $this->scopeCodeResolver->resolve($scopeType, $scopeId));
    }
}
