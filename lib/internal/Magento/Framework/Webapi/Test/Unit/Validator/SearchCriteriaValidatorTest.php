<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;
use Magento\Framework\Webapi\Validator\SearchCriteriaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verifies behavior of the search criteria validator
 */
class SearchCriteriaValidatorTest extends TestCase
{
    /**
     * @var IOLimitConfigProvider|MockObject
     */
    private $config;

    /**
     * @var SearchCriteriaValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->config = self::getMockBuilder(IOLimitConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new SearchCriteriaValidator(3, $this->config);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsPageSizeWhenAboveMinLimitAndBelowMaxLimit()
    {
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->validator->validateEntityValue(new SearchCriteria(), 'pageSize', 2);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsPageSizeWhenAboveMinLimitAndBelowMaxLimitUsingConfig()
    {
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->config->method('getMaximumPageSize')
            ->willReturn(50);
        $this->validator->validateEntityValue(new SearchCriteria(), 'pageSize', 25);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDisabledLimiting()
    {
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(false);
        $this->validator->validateEntityValue(new SearchCriteria(), 'pageSize', 1000);
    }

    public function testFailsPageSizeWhenAboveMaxLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Maximum SearchCriteria pageSize is 3');

        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->validator->validateEntityValue(new SearchCriteria(), 'pageSize', 4);
    }

    public function testFailsPageSizeWhenAboveMaxLimitUsingConfig()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Maximum SearchCriteria pageSize is 50');

        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->config->method('getMaximumPageSize')
            ->willReturn(50);
        $this->validator->validateEntityValue(new SearchCriteria(), 'pageSize', 100);
    }
}
