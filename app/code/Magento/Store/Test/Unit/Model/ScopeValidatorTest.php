<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeValidatorTest extends TestCase
{
    /**
     * @var ScopeValidator
     */
    protected $model;

    /**
     * @var ScopeResolverPool|MockObject
     */
    protected $scopeResolverPool;

    protected function setUp(): void
    {
        $this->scopeResolverPool = $this->getMockBuilder(ScopeResolverPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ScopeValidator(
            $this->scopeResolverPool
        );
    }

    public function testScopeDefault()
    {
        $scope = 'default';
        $scopeId = 0;

        $this->assertTrue($this->model->isValidScope($scope, $scopeId));
    }

    public function testInvalidScope()
    {
        $scope = 'websites';
        $scopeId = 1;

        $scopeObject = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $scopeObject->expects($this->once())
            ->method('getId')
            ->willReturn(false);

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

        $this->assertFalse($this->model->isValidScope($scope, $scopeId));
    }

    public function testInvalidScopeInvalidArgumentException()
    {
        $scope = 'websites';
        $scopeId = 1;

        $this->scopeResolverPool->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willThrowException(new \InvalidArgumentException());

        $this->assertFalse($this->model->isValidScope($scope, $scopeId));
    }

    public function testInvalidScopeNoSuchEntityException()
    {
        $scope = 'websites';
        $scopeId = 1;

        $this->scopeResolverPool->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willThrowException(new NoSuchEntityException(new Phrase('no such entity exception')));

        $this->assertFalse($this->model->isValidScope($scope, $scopeId));
    }
}
