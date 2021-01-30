<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\App\Scope;

use Magento\Framework\App\Scope\Source;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    /** @var Source */
    protected $model;

    /** @var ScopeResolverPool|MockObject */
    protected $scopeResolverPoolMock;

    /** @var string */
    protected $scope = 'scope';

    protected function setUp(): void
    {
        $this->scopeResolverPoolMock = $this->getMockBuilder(ScopeResolverPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Source($this->scopeResolverPoolMock, $this->scope);
    }

    public function testToOptionArray()
    {
        $scopeId = 1;
        $scopeName = 'Scope Name';
        $scopeData = [
            'value' => $scopeId,
            'label' => $scopeName,
        ];
        $result = [$scopeData, $scopeData];

        /** @var ScopeResolverInterface|MockObject $scopeResolverMock */
        $scopeResolverMock = $this->getMockBuilder(ScopeResolverInterface::class)
            ->getMockForAbstractClass();

        /** @var ScopeInterface|MockObject $scopeMock */
        $scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();

        $this->scopeResolverPoolMock->expects($this->once())
            ->method('get')
            ->with($this->scope)
            ->willReturn($scopeResolverMock);

        $scopeResolverMock->expects($this->once())
            ->method('getScopes')
            ->willReturn([$scopeMock, $scopeMock]);

        $scopeMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($scopeId);
        $scopeMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($scopeName);

        $this->assertEquals($result, $this->model->toOptionArray());
    }
}
