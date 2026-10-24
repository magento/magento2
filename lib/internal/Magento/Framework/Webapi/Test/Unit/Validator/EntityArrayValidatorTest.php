<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;
use Magento\Framework\Webapi\Validator\EntityArrayValidator;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verifies behavior of the entity array validator
 */
class EntityArrayValidatorTest extends TestCase
{
    /**
     * @var IOLimitConfigProvider|MockObject
     */
    private $configMock;

    /**
     * @var InputArraySizeLimitValue|MockObject
     */
    private $inputArraySizeLimitValueMock;

    /**
     * @var EntityArrayValidator
     */
    private EntityArrayValidator $validator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(IOLimitConfigProvider::class);
        $this->inputArraySizeLimitValueMock = $this->createMock(InputArraySizeLimitValue::class);
        $this->validator = new EntityArrayValidator(
            3,
            $this->configMock,
            $this->inputArraySizeLimitValueMock
        );
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testAllowsDataWhenBelowLimitWhenUsingRouteInputLimit(): void
    {
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->inputArraySizeLimitValueMock->expects(self::once())
            ->method('get')
            ->willReturn(5);
        $this->configMock->expects(self::never())
            ->method('getComplexArrayItemLimit');
        $this->validator->validateComplexArrayType("foo", array_fill(0, 5, []));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testFailsDataWhenAboveLimitUsingRouteInputLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum items of type "foo" is 4');
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->inputArraySizeLimitValueMock->expects(self::once())
            ->method('get')
            ->willReturn(4);
        $this->configMock->expects(self::never())
            ->method('getComplexArrayItemLimit');
        $this->validator->validateComplexArrayType("foo", array_fill(0, 5, []));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testAllowsDataWhenBelowLimit(): void
    {
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->inputArraySizeLimitValueMock->expects(self::once())
            ->method('get')
            ->willReturn(null);
        $this->configMock->expects(self::once())
            ->method('getComplexArrayItemLimit')
            ->willReturn(null);
        $this->validator->validateComplexArrayType("foo", array_fill(0, 3, []));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testAllowsDataWhenBelowLimitUsingConfig(): void
    {
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->inputArraySizeLimitValueMock->expects(self::once())
            ->method('get')
            ->willReturn(null);
        $this->configMock->expects(self::once())
            ->method('getComplexArrayItemLimit')
            ->willReturn(6);
        $this->validator->validateComplexArrayType("foo", array_fill(0, 5, []));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testFailsDataWhenAboveLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum items of type "foo" is 3');
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->inputArraySizeLimitValueMock->expects(self::once())
            ->method('get')
            ->willReturn(null);
        $this->configMock->expects(self::once())
            ->method('getComplexArrayItemLimit')
            ->willReturn(null);
        $this->validator->validateComplexArrayType("foo", array_fill(0, 4, []));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testFailsDataWhenAboveLimitUsingConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum items of type "foo" is 6');
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->inputArraySizeLimitValueMock->expects(self::once())
            ->method('get')
            ->willReturn(null);
        $this->configMock->expects(self::once())
            ->method('getComplexArrayItemLimit')
            ->willReturn(6);
        $this->validator->validateComplexArrayType("foo", array_fill(0, 7, []));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testAboveLimitWithDisabledLimiting(): void
    {
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(false);
        $this->configMock->expects(self::never())
            ->method('getComplexArrayItemLimit');
        $this->validator->validateComplexArrayType("foo", array_fill(0, 7, []));
    }
}
