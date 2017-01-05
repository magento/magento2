<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Scope\Validator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator
     */
    private $model;

    /**
     * @var ScopeResolverPool|MockObject
     */
    private $scopeResolverPoolMock;

    protected function setUp()
    {
        $this->scopeResolverPoolMock = $this->getMockBuilder(ScopeResolverPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Validator(
            $this->scopeResolverPoolMock
        );
    }

    public function testIsValid()
    {
        $scope = 'not_default_scope';
        $scopeCode = 'not_exist_scope_code';

        $scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->getMockForAbstractClass();
        $scopeObject = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($scopeCode)
            ->willReturn($scopeObject);
        $this->scopeResolverPoolMock->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willReturn($scopeResolver);

        $this->assertTrue($this->model->isValid($scope, $scopeCode));
    }

    public function testIsValidDefault()
    {
        $this->assertTrue($this->model->isValid(ScopeConfigInterface::SCOPE_TYPE_DEFAULT));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Scope code shouldn't be passed for scope "default"
     */
    public function testNotEmptyScopeCodeForDefaultScope()
    {
        $this->model->isValid(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 'some_code');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Scope can't be empty
     */
    public function testEmptyScope()
    {
        $this->model->isValid('', 'some_code');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Scope code can't be empty
     */
    public function testEmptyScopeCode()
    {
        $this->model->isValid('not_default_scope', '');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Wrong scope code format
     */
    public function testWrongScopeCodeFormat()
    {
        $this->model->isValid('not_default_scope', '123');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Scope "not_default_scope" doesn't exist
     */
    public function testScopeNotExist()
    {
        $scope = 'not_default_scope';
        $this->scopeResolverPoolMock->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willThrowException(new \InvalidArgumentException());

        $this->model->isValid($scope, 'scope_code');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Scope code "not_exist_scope_code" doesn't exist in scope "not_default_scope"
     */
    public function testScopeCodeNotExist()
    {
        $scope = 'not_default_scope';
        $scopeCode = 'not_exist_scope_code';

        $scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->getMockForAbstractClass();
        $scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($scopeCode)
            ->willThrowException(new NoSuchEntityException());
        $this->scopeResolverPoolMock->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willReturn($scopeResolver);

        $this->model->isValid($scope, $scopeCode);
    }
}
