<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilderInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\BucketInterface;

/**
 * @inheritdoc
 */
class Price implements LayerBuilderInterface
{
    /**
     * @var string
     */
    private const PRICE_BUCKET = 'price_bucket';

    /**
     * @var array
     */
    private static $bucketMap = [
        self::PRICE_BUCKET => [
            'request_name' => 'price',
            'label' => 'Price'
        ],
    ];

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $bucket = $aggregation->getBucket(self::PRICE_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $result = $this->buildLayer(
            self::$bucketMap[self::PRICE_BUCKET]['label'],
            \count($bucket->getValues()),
            self::$bucketMap[self::PRICE_BUCKET]['request_name']
        );

        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result['filter_items'][] = $this->buildItem(
                \str_replace('_', '-', $metrics['value']),
                $metrics['value'],
                $metrics['count']
            );
        }

        return [$result];
    }

    /**
     * Format layer data
     *
     * @param string $layerName
     * @param string $itemsCount
     * @param string $requestName
     * @return array
     */
    private function buildLayer($layerName, $itemsCount, $requestName): array
    {
        return [
            'name' => $layerName,
            'filter_items_count' => $itemsCount,
            'request_var' => $requestName
        ];
    }

    /**
     * Format layer item data
     *
     * @param string $label
     * @param string|int $value
     * @param string|int $count
     * @return array
     */
    private function buildItem($label, $value, $count): array
    {
        return [
            'label' => $label,
            'value_string' => $value,
            'items_count' => $count,
        ];
    }

    /**
     * Check that bucket contains data
     *
     * @param BucketInterface|null $bucket
     * @return bool
     */
    private function isBucketEmpty(?BucketInterface $bucket): bool
    {
        return null === $bucket || !$bucket->getValues();
    }
}
