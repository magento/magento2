<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\App\Scope;

use Magento\Framework\App\Scope\Source;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;

class SourceTest extends \PHPUnit\Framework\TestCase
{
    /** @var Source */
    protected $model;

    /** @var ScopeResolverPool|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeResolverPoolMock;

    /** @var string */
    protected $scope = 'scope';

    protected function setUp()
    {
        $this->scopeResolverPoolMock = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverPool::class)
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

        /** @var ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject $scopeResolverMock */
        $scopeResolverMock = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
            ->getMockForAbstractClass();

        /** @var ScopeInterface|\PHPUnit_Framework_MockObject_MockObject $scopeMock */
        $scopeMock = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
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
