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
    private $groupExcludedWebsiteRepository;

    /** @var Collection */
    private $ruleCollection;

    /** @var Rule */
    private $rule;

    /** @var RuleExtension */
    private $ruleExtension;

    /** @var Observer */
    private $observer;

    /** @var AddCustomerGroupExcludedWebsite */
    protected $addCustomerGroupExcludedWebsiteObserver;

    protected function setUp(): void
    {
        $this->groupExcludedWebsiteRepository = $this->getMockBuilder(GroupExcludedWebsiteRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->rule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleExtension = $this->getMockBuilder(RuleExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->observer->expects(self::atLeastOnce())
            ->method('getData')
            ->willReturn($this->ruleCollection);

        $this->addCustomerGroupExcludedWebsiteObserver = new AddCustomerGroupExcludedWebsite(
            $this->groupExcludedWebsiteRepository
        );
    }

    public function testExecuteWithoutCatalogRules(): void
    {
        $this->ruleCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([]);

        $this->groupExcludedWebsiteRepository->expects(self::never())
            ->method('getAllExcludedWebsites')
            ->willReturn([]);

        $this->addCustomerGroupExcludedWebsiteObserver->execute($this->observer);
    }

    public function testExecuteWithCustomerGroupExcludedWebsites(): void
    {
        $excludedWebsites = [
            1 => [2],
            3 => [1]
        ];
        $this->ruleCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$this->rule]);

        $this->groupExcludedWebsiteRepository->expects(self::once())
            ->method('getAllExcludedWebsites')
            ->willReturn($excludedWebsites);

        $this->rule->expects(self::once())
            ->method('getIsActive')
            ->willReturn(true);
        $this->rule->expects(self::once())
            ->method('getCustomerGroupIds')
            ->willReturn([1, 2, 3, 4]);

        $this->rule->expects(self::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->ruleExtension);
        $this->ruleExtension->expects(self::once())
            ->method('setExcludeWebsiteIds')
            ->with($excludedWebsites)
            ->willReturnSelf();
        $this->rule->expects(self::once())
            ->method('setExtensionAttributes')
            ->with($this->ruleExtension)
            ->willReturnSelf();

        $this->addCustomerGroupExcludedWebsiteObserver->execute($this->observer);
    }
}
