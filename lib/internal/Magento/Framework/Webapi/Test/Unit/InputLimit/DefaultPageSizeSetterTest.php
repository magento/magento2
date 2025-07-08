<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\InputLimit;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Webapi\Validator\IOLimit\DefaultPageSizeSetter;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test the page size is correctly set
 */
class DefaultPageSizeSetterTest extends TestCase
{
    /**
     * @var IOLimitConfigProvider|MockObject
     */
    private $configProvider;

    /**
     * @var DefaultPageSizeSetter
     */
    private $setter;

    protected function setUp(): void
    {
        $this->configProvider = $this->getMockBuilder(IOLimitConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setter = new DefaultPageSizeSetter($this->configProvider);
    }

    /**
     * @return void
     */
    public function testPageSizeIsNotSetWhenLimitingIsDisabled(): void
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(false);
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $searchCriteria->method('getPageSize')
            ->willReturn(null);
        $searchCriteria->expects(self::never())
            ->method('setPageSize');

        $this->setter->processSearchCriteria($searchCriteria);
    }

    /**
     * @return void
     */
    public function testPageSizeIsNotSetWhenAlreadySet(): void
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(true);
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $searchCriteria->method('getPageSize')
            ->willReturn(123);
        $searchCriteria->expects(self::never())
            ->method('setPageSize');

        $this->setter->processSearchCriteria($searchCriteria);
    }

    /**
     * @return void
     */
    public function testPageSizeIsSetWithPreferredConfigValue(): void
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->configProvider->method('getDefaultPageSize')
            ->willReturn(456);
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $searchCriteria->method('getPageSize')
            ->willReturn(null);

        $searchCriteria->expects(self::once())
            ->method('setPageSize')
            ->with(456);

        $this->setter->processSearchCriteria($searchCriteria, 678);
    }

    /**
     * @return void
     */
    public function testPageSizeIsSetWithPreferredFallbackValue(): void
    {
        $this->configProvider->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->configProvider->method('getDefaultPageSize')
            ->willReturn(null);
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $searchCriteria->method('getPageSize')
            ->willReturn(null);

        $searchCriteria->expects(self::once())
            ->method('setPageSize')
            ->with(678);

        $this->setter->processSearchCriteria($searchCriteria, 678);
    }
}
