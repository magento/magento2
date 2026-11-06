<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query\Resolver\Argument\SearchCriteria;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierPool;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * Verify that a null argument value is treated as omitted.
     */
    #[DataProvider('nullArgumentDataProvider')]
    public function testBuildSkipsNullArgument(string $argumentName): void
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteriaFactory = $this->createMock(SearchCriteriaInterfaceFactory::class);
        $searchCriteriaFactory->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteria);

        $applier = $this->createMock(ArgumentApplierInterface::class);
        $applier->expects(self::never())->method('applyArgument');

        $argumentApplierPool = $this->createMock(ArgumentApplierPool::class);
        $argumentApplierPool->method('hasApplier')
            ->willReturnCallback(fn(string $name): bool => $name === $argumentName);
        $argumentApplierPool->method('getApplier')->willReturn($applier);

        $builder = new Builder($searchCriteriaFactory, $argumentApplierPool);

        self::assertSame(
            $searchCriteria,
            $builder->build('products', [$argumentName => null, 'pageSize' => 20])
        );
    }

    /**
     * Verify that non-null arguments are still applied when another argument is null.
     */
    public function testBuildAppliesNonNullArguments(): void
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteriaFactory = $this->createMock(SearchCriteriaInterfaceFactory::class);
        $searchCriteriaFactory->expects(self::once())
            ->method('create')
            ->willReturn($searchCriteria);

        $sortApplier = $this->createMock(ArgumentApplierInterface::class);
        $sortApplier->expects(self::once())
            ->method('applyArgument')
            ->with($searchCriteria, 'products', 'sort', ['price' => 'ASC'])
            ->willReturn($searchCriteria);

        $filterApplier = $this->createMock(ArgumentApplierInterface::class);
        $filterApplier->expects(self::never())->method('applyArgument');

        $argumentApplierPool = $this->createMock(ArgumentApplierPool::class);
        $argumentApplierPool->method('hasApplier')
            ->willReturnCallback(fn(string $name): bool => in_array($name, ['sort', 'filter'], true));
        $argumentApplierPool->method('getApplier')
            ->willReturnCallback(fn(string $name): ArgumentApplierInterface => $name === 'sort'
                ? $sortApplier
                : $filterApplier);

        $builder = new Builder($searchCriteriaFactory, $argumentApplierPool);

        self::assertSame(
            $searchCriteria,
            $builder->build('products', ['filter' => null, 'sort' => ['price' => 'ASC']])
        );
    }

    /**
     * @return array<array<string>>
     */
    public static function nullArgumentDataProvider(): array
    {
        return [
            ['sort'],
            ['filter'],
        ];
    }
}
