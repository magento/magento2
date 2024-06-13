<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\CatalogRuleProductPriceRowSizeEstimator;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CatalogRuleProductPriceRowSizeEstimator
 */
class CatalogRuleProductPriceRowSizeEstimatorTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject
     */
    private $customerGroupCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $customerGroupCollectionMock;

    /**
     * @var CatalogRuleProductPriceRowSizeEstimator
     */
    private $estimator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->customerGroupCollectionMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getSize'])
            ->getMock();

        $collectionFactoryClass = 'Magento\Customer\Model\ResourceModel\Group\CollectionFactory';

        $this->customerGroupCollectionFactoryMock = $this->getMockBuilder($collectionFactoryClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->customerGroupCollectionFactoryMock->method('create')
            ->willReturn($this->customerGroupCollectionMock);

        $this->estimator = new CatalogRuleProductPriceRowSizeEstimator(
            $this->resourceConnectionMock,
            $this->customerGroupCollectionFactoryMock,
            $this->storeManagerMock
        );
    }

    /**
     * Test estimateRowSize with single website and single customer group
     *
     * @return void
     */
    public function testEstimateRowSizeWithSingleWebsiteAndGroup(): void
    {
        $customerGroupCount = 1;

        $this->customerGroupCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($customerGroupCount);

        $website = $this->createMock(Website::class);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$website]);

        $result = $this->estimator->estimateRowSize();

        // Expected: 1 customer group * 1 website * 2 * 150 bytes = 300 bytes
        $this->assertEquals(300, $result);
    }

    /**
     * Test estimateRowSize with multiple websites and customer groups
     *
     * @return void
     */
    public function testEstimateRowSizeWithMultipleWebsitesAndGroups(): void
    {
        $customerGroupCount = 4;
        $websiteCount = 3;

        $this->customerGroupCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($customerGroupCount);

        $websites = [];
        for ($i = 0; $i < $websiteCount; $i++) {
            $websites[] = $this->createMock(Website::class);
        }

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $result = $this->estimator->estimateRowSize();

        // Expected: 4 customer groups * 3 websites * 2 * 150 bytes = 3600 bytes
        $this->assertEquals(3600, $result);
    }

    /**
     * Test estimateRowSize returns integer value
     *
     * @return void
     */
    public function testEstimateRowSizeReturnsInteger(): void
    {
        $customerGroupCount = 3;
        $websiteCount = 2;

        $this->customerGroupCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($customerGroupCount);

        $websites = [];
        for ($i = 0; $i < $websiteCount; $i++) {
            $websites[] = $this->createMock(Website::class);
        }

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $result = $this->estimator->estimateRowSize();

        $this->assertIsInt($result);
        // Expected: 3 customer groups * 2 websites * 2 * 150 bytes = 1800 bytes
        $this->assertEquals(1800, $result);
    }

    /**
     * Test estimateRowSize with large number of groups and websites
     *
     * @return void
     */
    public function testEstimateRowSizeWithLargeConfiguration(): void
    {
        $customerGroupCount = 10;
        $websiteCount = 5;

        $this->customerGroupCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($customerGroupCount);

        $websites = [];
        for ($i = 0; $i < $websiteCount; $i++) {
            $websites[] = $this->createMock(Website::class);
        }

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $result = $this->estimator->estimateRowSize();

        // Expected: 10 customer groups * 5 websites * 2 * 150 bytes = 15000 bytes
        $this->assertEquals(15000, $result);
    }

    /**
     * Test estimateRowSize with zero customer groups
     *
     * @return void
     */
    public function testEstimateRowSizeWithZeroCustomerGroups(): void
    {
        $customerGroupCount = 0;
        $websiteCount = 3;

        $this->customerGroupCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($customerGroupCount);

        $websites = [];
        for ($i = 0; $i < $websiteCount; $i++) {
            $websites[] = $this->createMock(Website::class);
        }

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $result = $this->estimator->estimateRowSize();

        // Expected: 0 customer groups * 3 websites * 2 * 150 bytes = 0 bytes
        $this->assertEquals(0, $result);
    }

    /**
     * Test estimateRowSize with empty websites array
     *
     * @return void
     */
    public function testEstimateRowSizeWithNoWebsites(): void
    {
        $customerGroupCount = 5;

        $this->customerGroupCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($customerGroupCount);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([]);

        $result = $this->estimator->estimateRowSize();

        // Expected: 5 customer groups * 0 websites * 2 * 150 bytes = 0 bytes
        $this->assertEquals(0, $result);
    }
}
