<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\CatalogRule\Observer\PrepareCatalogProductCollectionPricesObserver;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrepareCatalogProductCollectionPricesObserverTest extends TestCase
{
    /**
     * @var PrepareCatalogProductCollectionPricesObserver
     */
    private $observer;

    /**
     * @var RulePricesStorage
     */
    private $rulePricesStorage;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var Rule
     */
    private $ruleResource;

    protected function setUp(): void
    {
        $this->rulePricesStorage = $this->createMock(RulePricesStorage::class);
        $this->localeDate = $this->createMock(TimezoneInterface::class);
        $this->ruleResource = $this->createMock(Rule::class);

        $ruleFactory = $this->createMock(RuleFactory::class);
        $ruleFactory->method('create')->willReturn($this->ruleResource);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $store->method('getWebsiteId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $customerSession = $this->createMock(Session::class);
        $customerSession->method('isLoggedIn')->willReturn(true);
        $customerSession->method('getCustomerGroupId')->willReturn(0);

        $groupManagement = $this->createMock(GroupManagementInterface::class);

        $this->observer = new PrepareCatalogProductCollectionPricesObserver(
            $this->rulePricesStorage,
            $ruleFactory,
            $storeManager,
            $this->localeDate,
            $customerSession,
            $groupManagement
        );
    }

    /**
     * The keys written here are read back by ProcessFrontFinalPriceObserver and by
     * CatalogRulePrice, both of which derive the date from
     * TimezoneInterface::scopeDate(). Deriving it any other way produces keys that
     * never match, so the prefetched prices are never used.
     */
    public function testStoresPricesUnderScopeDateDerivedKey(): void
    {
        $date = new \DateTime('2026-01-01 00:00:00');
        $this->localeDate->expects($this->once())
            ->method('scopeDate')
            ->with(1)
            ->willReturn($date);
        $this->localeDate->expects($this->never())->method('scopeTimeStamp');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(7);

        $this->rulePricesStorage->method('hasRulePrice')->willReturn(false);
        $this->ruleResource->expects($this->once())
            ->method('getRulePrices')
            ->with($date, 1, 0, [7])
            ->willReturn([7 => 9.99]);

        $this->rulePricesStorage->expects($this->once())
            ->method('setRulePrice')
            ->with('2026-01-01 00:00:00|1|0|7', 9.99);

        // Event and Observer expose their data through DataObject magic methods,
        // which cannot be stubbed, so real instances are used here.
        $event = new Event(['collection' => [$product], 'store_id' => 1]);

        $this->observer->execute(new Observer(['event' => $event]));
    }
}
