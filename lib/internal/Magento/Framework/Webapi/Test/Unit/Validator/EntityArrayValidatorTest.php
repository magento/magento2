<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @doesNotPerformAssertions
     */
    public function testAllowsDataWhenBelowLimitWhenUsingRouteInputLimit()
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
     * @doesNotPerformAssertions
     */
    public function testFailsDataWhenAboveLimitUsingRouteInputLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Maximum items of type "foo" is 4');
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
     * @doesNotPerformAssertions
     */
    public function testAllowsDataWhenBelowLimit()
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
     * @doesNotPerformAssertions
     */
    public function testAllowsDataWhenBelowLimitUsingConfig()
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
     * @doesNotPerformAssertions
     */
    public function testFailsDataWhenAboveLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Maximum items of type "foo" is 3');
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
     * @doesNotPerformAssertions
     */
    public function testFailsDataWhenAboveLimitUsingConfig()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Maximum items of type "foo" is 6');
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
     * @doesNotPerformAssertions
     */
    public function testAboveLimitWithDisabledLimiting()
    {
        $this->configMock->expects(self::once())
            ->method('isInputLimitingEnabled')
            ->willReturn(false);
        $this->configMock->expects(self::never())
            ->method('getComplexArrayItemLimit');
        $this->validator->validateComplexArrayType("foo", array_fill(0, 7, []));
    }
}
