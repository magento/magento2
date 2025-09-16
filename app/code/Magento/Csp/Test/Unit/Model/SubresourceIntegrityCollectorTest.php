<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for Class @see Magento\Csp\Model\SubresourceIntegrityCollector
 */
class SubresourceIntegrityCollectorTest extends TestCase
{
    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $collector;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        $this->collector = new SubresourceIntegrityCollector();
    }

    /**
     * Test that collect method adds integrity objects to internal storage
     */
    public function testCollectAddsIntegrityObject(): void
    {
        $integrityMock = $this->createMock(SubresourceIntegrity::class);

        $this->collector->collect($integrityMock);

        $result = $this->collector->release();
        $this->assertCount(1, $result);
        $this->assertSame($integrityMock, $result[0]);
    }

    /**
     * Test that multiple collect calls accumulate objects
     */
    public function testMultipleCollectCallsAccumulate(): void
    {
        $integrity1 = $this->createMock(SubresourceIntegrity::class);
        $integrity2 = $this->createMock(SubresourceIntegrity::class);
        $integrity3 = $this->createMock(SubresourceIntegrity::class);

        $this->collector->collect($integrity1);
        $this->collector->collect($integrity2);
        $this->collector->collect($integrity3);

        $result = $this->collector->release();
        $this->assertCount(3, $result);
        $this->assertSame($integrity1, $result[0]);
        $this->assertSame($integrity2, $result[1]);
        $this->assertSame($integrity3, $result[2]);
    }

    /**
     * Test that release returns empty array when no objects collected
     */
    public function testReleaseReturnsEmptyArrayWhenEmpty(): void
    {
        $result = $this->collector->release();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that release does not clear the internal data
     */
    public function testReleaseDoesNotClearData(): void
    {
        $integrityMock = $this->createMock(SubresourceIntegrity::class);

        $this->collector->collect($integrityMock);

        // Call release multiple times
        $result1 = $this->collector->release();
        $result2 = $this->collector->release();

        $this->assertCount(1, $result1);
        $this->assertCount(1, $result2);
        $this->assertSame($integrityMock, $result1[0]);
        $this->assertSame($integrityMock, $result2[0]);
    }

    /**
     * Test that clear method empties the internal storage
     */
    public function testClearEmptiesInternalStorage(): void
    {
        $integrity1 = $this->createMock(SubresourceIntegrity::class);
        $integrity2 = $this->createMock(SubresourceIntegrity::class);

        // Add some objects
        $this->collector->collect($integrity1);
        $this->collector->collect($integrity2);

        // Verify they're there
        $resultBeforeClear = $this->collector->release();
        $this->assertCount(2, $resultBeforeClear);

        // Clear the data
        $this->collector->clear();

        // Verify it's empty
        $resultAfterClear = $this->collector->release();
        $this->assertEmpty($resultAfterClear);
    }

    /**
     * Test that collector works properly after clear
     */
    public function testCollectorWorksAfterClear(): void
    {
        $integrity1 = $this->createMock(SubresourceIntegrity::class);
        $integrity2 = $this->createMock(SubresourceIntegrity::class);

        // Add and clear
        $this->collector->collect($integrity1);
        $this->collector->clear();

        // Add new data
        $this->collector->collect($integrity2);

        $result = $this->collector->release();
        $this->assertCount(1, $result);
        $this->assertSame($integrity2, $result[0]);
    }

    /**
     * Test collect, release, clear cycle
     */
    public function testCompleteCollectReleaseClearCycle(): void
    {
        $integrity1 = $this->createMock(SubresourceIntegrity::class);
        $integrity2 = $this->createMock(SubresourceIntegrity::class);

        // Initially empty
        $this->assertEmpty($this->collector->release());

        // Collect objects
        $this->collector->collect($integrity1);
        $this->collector->collect($integrity2);

        // Release should return collected objects
        $released = $this->collector->release();
        $this->assertCount(2, $released);
        $this->assertSame($integrity1, $released[0]);
        $this->assertSame($integrity2, $released[1]);

        // Data should still be there after release
        $this->assertCount(2, $this->collector->release());

        // Clear should empty everything
        $this->collector->clear();
        $this->assertEmpty($this->collector->release());
    }
}
