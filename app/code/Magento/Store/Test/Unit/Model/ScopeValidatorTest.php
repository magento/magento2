<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeValidator;

class ScopeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeValidator
     */
    protected $model;

    /**
     * @var ScopeResolverPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolverPool;

    protected function setUp()
    {
        $this->scopeResolverPool = $this->getMockBuilder('Magento\Framework\App\ScopeResolverPool')
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

        $scopeObject = $this->getMockBuilder('Magento\Framework\App\ScopeInterface')
            ->getMockForAbstractClass();
        $scopeObject->expects($this->once())
            ->method('getId')
            ->willReturn(false);

        $scopeResolver = $this->getMockBuilder('Magento\Framework\App\ScopeResolverInterface')
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
