<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Scope\Validator;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Validator
     */
    private $model;

    /**
     * @var ScopeResolverPool|MockObject
     */
    private $scopeResolverPoolMock;

    /**
     * @inheritdoc
     */
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
     * @expectedExceptionMessage The "default" scope can't include a scope code. Try again without entering a scope
     */
    public function testNotEmptyScopeCodeForDefaultScope()
    {
        $this->model->isValid(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 'some_code');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage A scope is missing. Enter a scope and try again.
     */
    public function testEmptyScope()
    {
        $this->model->isValid('', 'some_code');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage A scope code is missing. Enter a code and try again.
     */
    public function testEmptyScopeCode()
    {
        $this->model->isValid('not_default_scope', '');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The scope code can include only lowercase letters (a-z), numbers (0-9) and underscores
     */
    public function testWrongScopeCodeFormat()
    {
        $this->model->isValid('not_default_scope', '123');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The "not_default_scope" value doesn't exist. Enter another value and try again.
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
     * @expectedExceptionMessage The "not_exist_scope_code" value doesn't exist. Enter another value and try again.
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
