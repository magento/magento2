<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model;

use Magento\CatalogGraphQl\Model\AggregationOptionTypeResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AggregationOptionTypeResolverTest extends TestCase
{
    /**
     * @param array $data
     * @param string $expectedType
     * @return void
     */
    #[DataProvider('resolveTypeDataProvider')]
    public function testResolveType(array $data, string $expectedType): void
    {
        $resolver = new AggregationOptionTypeResolver();

        self::assertSame($expectedType, $resolver->resolveType($data));
    }

    /**
     * @return array
     */
    public static function resolveTypeDataProvider(): array
    {
        $option = [
            'label' => 'Purple',
            'value' => 'purple',
            'count' => 2,
        ];

        return [
            'aggregation option' => [$option, 'AggregationOption'],
            'extended aggregation option' => [
                $option + [
                    'swatch' => [
                        'type' => '2',
                        'value' => 'purple.png',
                    ],
                ],
                'AggregationOption',
            ],
            'missing label' => [
                [
                    'value' => 'purple',
                    'count' => 2,
                ],
                '',
            ],
        ];
    }
}
