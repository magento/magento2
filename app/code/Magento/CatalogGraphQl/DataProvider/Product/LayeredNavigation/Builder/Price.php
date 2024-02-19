<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\AttributeOptionProvider;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilderInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Zend_Db_Statement_Exception;

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
     * @var LayerFormatter
     */
    private $layerFormatter;

    /**
     * @var AttributeOptionProvider
     */
    private $attributeOptionProvider;

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
     * @param LayerFormatter $layerFormatter
     * @param AttributeOptionProvider $attributeOptionProvider
     */
    public function __construct(
        LayerFormatter $layerFormatter,
        AttributeOptionProvider $attributeOptionProvider
    ) {
        $this->layerFormatter = $layerFormatter;
        $this->attributeOptionProvider = $attributeOptionProvider;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $attributeOptions = $this->getAttributeOptions($aggregation, $storeId);
        $attributeCode = self::$bucketMap[self::PRICE_BUCKET]['request_name'];
        $attribute = $attributeOptions[$attributeCode] ?? [];
        $bucket = $aggregation->getBucket(self::PRICE_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $result = $this->layerFormatter->buildLayer(
            $attribute['attribute_label'] ?? self::$bucketMap[self::PRICE_BUCKET]['label'],
            \count($bucket->getValues()),
            self::$bucketMap[self::PRICE_BUCKET]['request_name']
        );

        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result['options'][] = $this->layerFormatter->buildItem(
                isset($metrics['value']) ? \str_replace('_', '-', $metrics['value']) : '',
                $metrics['value'],
                $metrics['count']
            );
        }

        return [self::PRICE_BUCKET => $result];
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

    /**
     * Get list of attributes with options
     *
     * @param AggregationInterface $aggregation
     * @param int|null $storeId
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    private function getAttributeOptions(AggregationInterface $aggregation, ?int $storeId): array
    {
        $attributeOptionIds = [];
        $attributes = [];

        $bucket = $aggregation->getBucket(self::PRICE_BUCKET);

        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $attributes[] = \preg_replace('~_bucket$~', '', $bucket->getName());
        $attributeOptionIds[] = \array_map(
            function (AggregationValueInterface $value) {
                return $value->getValue();
            },
            $bucket->getValues()
        );

        return $this->attributeOptionProvider->getOptions(
            \array_merge([], ...$attributeOptionIds),
            $storeId,
            $attributes
        );
    }
}
