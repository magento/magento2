<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Plugin\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Weee\Model\Tax;
use Magento\Weee\Plugin\Model\ConfigurableVariationAttributePriority;
use PHPUnit\Framework\TestCase;

/**
 * Coverage for the per-request caching introduced by #40642.
 */
class ConfigurableVariationAttributePriorityTest extends TestCase
{
    public function testParentIdsAndParentLoadsAreCachedAcrossCalls(): void
    {
        $parentProduct = $this->createMock(ProductInterface::class);

        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->expects($this->once())
            ->method('getById')
            ->with(100)
            ->willReturn($parentProduct);

        $configurable = $this->createMock(Configurable::class);
        $configurable->expects($this->once())
            ->method('getParentIdsByChild')
            ->with(1)
            ->willReturn([100]);

        $weeeAttrs = [['code' => 'tax', 'amount' => 5]];

        $subject = $this->createMock(Tax::class);
        $subject->expects($this->once())
            ->method('getProductWeeeAttributes')
            ->with($parentProduct, null, null, null, null, true)
            ->willReturn($weeeAttrs);

        $plugin = new ConfigurableVariationAttributePriority($productRepository, $configurable);

        $child = $this->createMock(ProductInterface::class);
        $child->method('getId')->willReturn(1);

        $first = $plugin->afterGetProductWeeeAttributes($subject, [], $child);
        $second = $plugin->afterGetProductWeeeAttributes($subject, [], $child);

        $this->assertSame($weeeAttrs, $first);
        $this->assertSame($weeeAttrs, $second);
    }

    public function testHandlesNonBooleanScopeArgumentsWithoutTypeError(): void
    {
        $parentProduct = $this->createMock(ProductInterface::class);

        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->method('getById')->with(100)->willReturn($parentProduct);

        $configurable = $this->createMock(Configurable::class);
        $configurable->method('getParentIdsByChild')->with(1)->willReturn([100]);

        $weeeAttrs = [['code' => 'tax', 'amount' => 5]];

        $subject = $this->createMock(Tax::class);
        $subject->method('getProductWeeeAttributes')->willReturn($weeeAttrs);

        $plugin = new ConfigurableVariationAttributePriority($productRepository, $configurable);

        $child = $this->createMock(ProductInterface::class);
        $child->method('getId')->willReturn(1);

        // $calculateTax and $round arrive as ints from untyped Tax::getProductWeeeAttributes callers.
        $result = $plugin->afterGetProductWeeeAttributes($subject, [], $child, null, null, 1, 1, 0);

        $this->assertSame($weeeAttrs, $result);
    }

    public function testReturnsExistingResultWithoutLookupWhenNotEmpty(): void
    {
        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->expects($this->never())->method('getById');

        $configurable = $this->createMock(Configurable::class);
        $configurable->expects($this->never())->method('getParentIdsByChild');

        $subject = $this->createMock(Tax::class);
        $subject->expects($this->never())->method('getProductWeeeAttributes');

        $plugin = new ConfigurableVariationAttributePriority($productRepository, $configurable);

        $child = $this->createMock(ProductInterface::class);
        $existing = [['code' => 'eco', 'amount' => 1]];

        $this->assertSame($existing, $plugin->afterGetProductWeeeAttributes($subject, $existing, $child));
    }

    public function testReturnsFirstNonEmptyParentResultAndStopsIterating(): void
    {
        $parentEmpty = $this->createMock(ProductInterface::class);
        $parentWithAttrs = $this->createMock(ProductInterface::class);

        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->method('getById')
            ->willReturnMap([
                [200, null, false, $parentEmpty],
                [201, null, false, $parentWithAttrs],
            ]);

        $configurable = $this->createMock(Configurable::class);
        $configurable->method('getParentIdsByChild')->willReturn([200, 201, 202]);

        $weeeAttrs = [['code' => 'tax', 'amount' => 7]];

        $subject = $this->createMock(Tax::class);
        $subject->method('getProductWeeeAttributes')
            ->willReturnCallback(function ($product) use ($parentEmpty, $weeeAttrs) {
                return $product === $parentEmpty ? [] : $weeeAttrs;
            });

        $plugin = new ConfigurableVariationAttributePriority($productRepository, $configurable);

        $child = $this->createMock(ProductInterface::class);
        $child->method('getId')->willReturn(50);

        $result = $plugin->afterGetProductWeeeAttributes($subject, [], $child);
        $this->assertSame($weeeAttrs, $result);
    }
}
