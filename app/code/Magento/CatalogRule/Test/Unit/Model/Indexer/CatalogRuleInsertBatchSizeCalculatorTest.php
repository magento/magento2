<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\CatalogRuleInsertBatchSizeCalculator;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\BatchSizeManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CatalogRuleInsertBatchSizeCalculator
 */
class CatalogRuleInsertBatchSizeCalculatorTest extends TestCase
{
    /**
     * @var BatchSizeManagementInterface|MockObject
     */
    private $batchSizeManagementMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var CatalogRuleInsertBatchSizeCalculator
     */
    private $calculator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->batchSizeManagementMock = $this->createMock(BatchSizeManagementInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
    }

    public function testGetInsertBatchSizeWithDefaultBatchSize(): void
    {
        $defaultBatchSize = 5000;
        $this->calculator = new CatalogRuleInsertBatchSizeCalculator(
            $this->batchSizeManagementMock,
            $defaultBatchSize
        );

        $this->batchSizeManagementMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($this->connectionMock, $defaultBatchSize);

        $result = $this->calculator->getInsertBatchSize($this->connectionMock);
        $this->assertEquals($defaultBatchSize, $result);
    }

    public function testGetInsertBatchSizeWithCustomBatchSize(): void
    {
        $customBatchSize = 10000;
        $this->calculator = new CatalogRuleInsertBatchSizeCalculator(
            $this->batchSizeManagementMock,
            $customBatchSize
        );

        $this->batchSizeManagementMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($this->connectionMock, $customBatchSize);

        $result = $this->calculator->getInsertBatchSize($this->connectionMock);
        $this->assertEquals($customBatchSize, $result);
    }

    public function testGetInsertBatchSizeWithAdjustedBatchSize(): void
    {
        $defaultBatchSize = 5000;
        $this->calculator = new CatalogRuleInsertBatchSizeCalculator(
            $this->batchSizeManagementMock,
            $defaultBatchSize
        );

        $this->batchSizeManagementMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($this->connectionMock, $defaultBatchSize);

        $result = $this->calculator->getInsertBatchSize($this->connectionMock);
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetInsertBatchSizeReturnsInteger(): void
    {
        $defaultBatchSize = 5000;
        $this->calculator = new CatalogRuleInsertBatchSizeCalculator(
            $this->batchSizeManagementMock,
            $defaultBatchSize
        );

        $this->batchSizeManagementMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($this->connectionMock, $defaultBatchSize);

        $result = $this->calculator->getInsertBatchSize($this->connectionMock);
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }
}
