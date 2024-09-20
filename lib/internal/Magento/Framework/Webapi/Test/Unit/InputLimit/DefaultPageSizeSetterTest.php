<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @doesNotPerformAssertions
     */
    public function testPageSizeIsNotSetWhenLimitingIsDisabled()
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
     * @doesNotPerformAssertions
     */
    public function testPageSizeIsNotSetWhenAlreadySet()
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
     * @doesNotPerformAssertions
     */
    public function testPageSizeIsSetWithPreferredConfigValue()
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
     * @doesNotPerformAssertions
     */
    public function testPageSizeIsSetWithPreferredFallbackValue()
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
