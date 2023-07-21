<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer\CatalogRule;

use Magento\CatalogRule\Api\Data\RuleExtension;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\Rule;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Observer\CatalogRule\AddCustomerGroupExcludedWebsite;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddCustomerGroupExcludedWebsiteTest extends TestCase
{
    /** @var GroupExcludedWebsiteRepositoryInterface|MockObject */
    private $groupExcludedWebsiteRepositoryMock;

    /** @var Collection */
    private $ruleCollectionMock;

    /** @var Rule */
    private $ruleMock;

    /** @var RuleExtension */
    private $ruleExtensionMock;

    /** @var Observer */
    private $observerMock;

    /** @var AddCustomerGroupExcludedWebsite */
    private $observer;

    protected function setUp(): void
    {
        $this->groupExcludedWebsiteRepositoryMock = $this->getMockBuilder(GroupExcludedWebsiteRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleExtensionMock = $this->getMockBuilder(RuleExtension::class)
            ->addMethods(['setExcludeWebsiteIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->observerMock->expects(self::atLeastOnce())
            ->method('getData')
            ->willReturn($this->ruleCollectionMock);

        $this->observer = new AddCustomerGroupExcludedWebsite($this->groupExcludedWebsiteRepositoryMock);
    }

    public function testExecuteWithoutCatalogRules(): void
    {
        $this->ruleCollectionMock->expects(self::once())
            ->method('getItems')
            ->willReturn([]);

        $this->groupExcludedWebsiteRepositoryMock->expects(self::never())
            ->method('getAllExcludedWebsites')
            ->willReturn([]);

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteWithCustomerGroupExcludedWebsites(): void
    {
        $excludedWebsites = [
            1 => [2],
            3 => [1]
        ];
        $this->ruleCollectionMock->expects(self::once())
            ->method('getItems')
            ->willReturn([$this->ruleMock]);

        $this->groupExcludedWebsiteRepositoryMock->expects(self::once())
            ->method('getAllExcludedWebsites')
            ->willReturn($excludedWebsites);

        $this->ruleMock->expects(self::once())
            ->method('getIsActive')
            ->willReturn(true);
        $this->ruleMock->expects(self::once())
            ->method('getCustomerGroupIds')
            ->willReturn([1, 2, 3, 4]);

        $this->ruleMock->expects(self::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->ruleExtensionMock);
        $this->ruleExtensionMock->expects(self::once())
            ->method('setExcludeWebsiteIds')
            ->with($excludedWebsites)
            ->willReturnSelf();
        $this->ruleMock->expects(self::once())
            ->method('setExtensionAttributes')
            ->with($this->ruleExtensionMock)
            ->willReturnSelf();

        $this->observer->execute($this->observerMock);
    }
}
