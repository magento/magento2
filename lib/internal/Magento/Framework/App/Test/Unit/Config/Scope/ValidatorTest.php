<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\Validator;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated As tested model class was moved to another directory,
 *             unit test was created in the appropriate directory.
 * @see \Magento\Framework\App\Test\Unit\Scope\ValidatorTest
 */
class ValidatorTest extends TestCase
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
    protected function setUp(): void
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

    public function testNotEmptyScopeCodeForDefaultScope()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The "default" scope can\'t include a scope code. Try again without entering a scope'
        );
        $this->model->isValid(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 'some_code');
    }

    public function testEmptyScope()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('A scope is missing. Enter a scope and try again.');
        $this->model->isValid('', 'some_code');
    }

    public function testEmptyScopeCode()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('A scope code is missing. Enter a code and try again.');
        $this->model->isValid('not_default_scope', '');
    }

    public function testWrongScopeCodeFormat()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The scope code can include only letters (a-z), numbers (0-9) and underscores'
        );
        $this->model->isValid('not_default_scope', '123');
    }

    public function testScopeNotExist()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The "not_default_scope" value doesn\'t exist. Enter another value and try again.'
        );
        $scope = 'not_default_scope';
        $this->scopeResolverPoolMock->expects($this->once())
            ->method('get')
            ->with($scope)
            ->willThrowException(new \InvalidArgumentException());

        $this->model->isValid($scope, 'scope_code');
    }

    public function testScopeCodeNotExist()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The "not_exist_scope_code" value doesn\'t exist. Enter another value and try again.'
        );
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
