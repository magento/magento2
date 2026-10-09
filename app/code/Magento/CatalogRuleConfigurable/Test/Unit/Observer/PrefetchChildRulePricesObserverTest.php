<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Unit\Observer;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\CatalogRuleConfigurable\Observer\PrefetchChildRulePricesObserver;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrefetchChildRulePricesObserverTest extends TestCase
{
    /**
     * @var PrefetchChildRulePricesObserver
     */
    private $observer;

    /**
     * @var RulePricesStorage
     */
    private $rulePricesStorage;

    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var ConfigurableResource
     */
    private $configurableResource;

    protected function setUp(): void
    {
        $this->rulePricesStorage = $this->createMock(RulePricesStorage::class);
        $this->ruleResource = $this->createMock(Rule::class);

        $ruleFactory = $this->createMock(RuleFactory::class);
        $ruleFactory->method('create')->willReturn($this->ruleResource);

        $this->configurableResource = $this->createMock(ConfigurableResource::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $store->method('getWebsiteId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $localeDate = $this->createMock(TimezoneInterface::class);
        $localeDate->method('scopeDate')->willReturn(new \DateTime('2026-01-01 00:00:00'));

        $customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $customerSession->method('getCustomerGroupId')->willReturn(0);

        $this->observer = new PrefetchChildRulePricesObserver(
            $this->rulePricesStorage,
            $ruleFactory,
            $this->configurableResource,
            $storeManager,
            $localeDate,
            $customerSession
        );
    }

    /**
     * The children of the configurable products in the collection are fetched in one query and
     * stored under the key their price models will look them up by.
     */
    public function testPrefetchesChildPricesInOneQuery(): void
    {
        $parent = new DataObject(['id' => 7, 'type_id' => 'configurable']);

        $this->configurableResource->expects($this->once())
            ->method('getChildrenIds')
            ->with([7])
            ->willReturn([0 => ['101' => '101', '102' => '102']]);
        $this->rulePricesStorage->method('hasRulePrice')->willReturn(false);

        $this->ruleResource->expects($this->once())
            ->method('getRulePrices')
            ->with($this->anything(), 1, 0, [101, 102])
            ->willReturn([101 => 9.99]);

        $stored = [];
        $this->rulePricesStorage->method('setRulePrice')
            ->willReturnCallback(function ($key, $price) use (&$stored) {
                $stored[$key] = $price;
            });

        $this->observer->execute(
            new Observer(['event' => new Event(['collection' => [$parent], 'store_id' => 1])])
        );

        $this->assertSame(
            ['2026-01-01 00:00:00|1|0|101' => 9.99, '2026-01-01 00:00:00|1|0|102' => false],
            $stored
        );
    }

    /**
     * A customer group and date passed with the event take precedence over the session and the
     * current date, as they do in PrepareCatalogProductCollectionPricesObserver.
     */
    public function testUsesCustomerGroupAndDateFromEvent(): void
    {
        $parent = new DataObject(['id' => 7, 'type_id' => 'configurable']);

        $this->configurableResource->method('getChildrenIds')->willReturn([0 => ['101' => '101']]);
        $this->rulePricesStorage->method('hasRulePrice')->willReturn(false);

        $this->ruleResource->expects($this->once())
            ->method('getRulePrices')
            ->with(new \DateTime('2026-03-15 00:00:00'), 1, 3, [101])
            ->willReturn([101 => 5.5]);
        $this->rulePricesStorage->expects($this->once())
            ->method('setRulePrice')
            ->with('2026-03-15 00:00:00|1|3|101', 5.5);

        $this->observer->execute(
            new Observer([
                'event' => new Event([
                    'collection' => [$parent],
                    'store_id' => 1,
                    'customer_group_id' => 3,
                    'date' => '2026-03-15',
                ]),
            ])
        );
    }

    /**
     * A collection without configurable products must not query anything.
     */
    public function testDoesNothingWithoutConfigurableProducts(): void
    {
        $this->configurableResource->expects($this->never())->method('getChildrenIds');
        $this->ruleResource->expects($this->never())->method('getRulePrices');

        $this->observer->execute(
            new Observer([
                'event' => new Event([
                    'collection' => [new DataObject(['id' => 1, 'type_id' => 'simple'])],
                    'store_id' => 1,
                ]),
            ])
        );
    }
}
