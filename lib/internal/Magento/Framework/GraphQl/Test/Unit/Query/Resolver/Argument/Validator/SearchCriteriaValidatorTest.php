<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query\Resolver\Argument\Validator;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\IOLimit\IOLimitConfigProvider;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\SearchCriteriaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of graphql search criteria validator
 */
class SearchCriteriaValidatorTest extends TestCase
{
    /**
     * @var IOLimitConfigProvider|MockObject
     */
    private $configProvider;

    /**
     * @var SearchCriteriaValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->configProvider = self::getMockBuilder(IOLimitConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new SearchCriteriaValidator(3, $this->configProvider);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidValueWithDisabledDefaultConfig()
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(false);
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->validate($field, ['pageSize' => 50]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidValue()
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(false);
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->validate($field, ['pageSize' => 3]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidValueWithConfig()
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->configProvider->method('getMaximumPageSize')
            ->willReturn(10);

        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->validate($field, ['pageSize' => 10]);
    }

    public function testInvalidMaxValue()
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Maximum pageSize is 3");

        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(true);
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->validate($field, ['pageSize' => 4]);
    }

    public function testInvalidValueWithConfig()
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Maximum pageSize is 10");

        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->configProvider->method('getMaximumPageSize')
            ->willReturn(10);

        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->validate($field, ['pageSize' => 11]);
    }
}
