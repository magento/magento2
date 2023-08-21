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
use Magento\Config\Model\Config\Source\Yesno;

/**
 * @inheritdoc
 */
class Attribute implements LayerBuilderInterface
{
    /**
     * @var string
     * @see \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Category::CATEGORY_BUCKET
     */
    private const PRICE_BUCKET = 'price_bucket';

    /**
     * @var string
     * @see \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price::PRICE_BUCKET
     */
    private const CATEGORY_BUCKET = 'category_bucket';

    /**
     * @var AttributeOptionProvider
     */
    private $attributeOptionProvider;

    /**
     * @var LayerFormatter
     */
    private $layerFormatter;

    /**
     * @var array
     */
    private $bucketNameFilter = [
        self::PRICE_BUCKET,
        self::CATEGORY_BUCKET
    ];

    /**
     * @var Yesno
     */
    private Yesno $YesNo;

    /**
     * @param AttributeOptionProvider $attributeOptionProvider
     * @param LayerFormatter $layerFormatter
     * @param Yesno $YesNo
     * @param array $bucketNameFilter
     */
    public function __construct(
        AttributeOptionProvider $attributeOptionProvider,
        LayerFormatter $layerFormatter,
        Yesno $YesNo,
        $bucketNameFilter = []
    ) {
        $this->attributeOptionProvider = $attributeOptionProvider;
        $this->layerFormatter = $layerFormatter;
        $this->YesNo = $YesNo;
        $this->bucketNameFilter = \array_merge($this->bucketNameFilter, $bucketNameFilter);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Zend_Db_Statement_Exception
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $attributeOptions = $this->getAttributeOptions($aggregation, $storeId);

        // build layer per attribute
        $result = [];
        foreach ($this->getAttributeBuckets($aggregation) as $bucket) {
            $bucketName = $bucket->getName();
            $attributeCode = \preg_replace('~_bucket$~', '', $bucketName);
            $attribute = $attributeOptions[$attributeCode] ?? [];

            $result[$bucketName] = $this->layerFormatter->buildLayer(
                $attribute['attribute_label'] ?? $bucketName,
                \count($bucket->getValues()),
                $attribute['attribute_code'] ?? $bucketName,
                isset($attribute['position']) ? $attribute['position'] : null
            );

            $options = $this->getSortedOptions(
                $bucket,
                isset($attribute['options']) ? $attribute['options'] : [],
                ($attribute['attribute_type']) ? $attribute['attribute_type']: ''
            );
            foreach ($options as $option) {
                $result[$bucketName]['options'][] = $this->layerFormatter->buildItem(
                    $option['label'],
                    $option['value'],
                    $option['count']
                );
            }
        }

        return $result;
    }

    /**
     * Get attribute buckets excluding specified bucket names
     *
     * @param AggregationInterface $aggregation
     * @return \Generator|BucketInterface[]
     */
    private function getAttributeBuckets(AggregationInterface $aggregation)
    {
        foreach ($aggregation->getBuckets() as $bucket) {
            if (\in_array($bucket->getName(), $this->bucketNameFilter, true)) {
                continue;
            }
            if ($this->isBucketEmpty($bucket)) {
                continue;
            }
            yield $bucket;
        }
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
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAttributeOptions(AggregationInterface $aggregation, ?int $storeId): array
    {
        $attributeOptionIds = [];
        $attributes = [];
        foreach ($this->getAttributeBuckets($aggregation) as $bucket) {
            $attributes[] = \preg_replace('~_bucket$~', '', $bucket->getName());
            $attributeOptionIds[] = \array_map(
                function (AggregationValueInterface $value) {
                    return $value->getValue();
                },
                $bucket->getValues()
            );
        }

        if (!$attributeOptionIds) {
            return [];
        }

        return $this->attributeOptionProvider->getOptions(
            \array_merge([], ...$attributeOptionIds),
            $storeId,
            $attributes
        );
    }

    /**
     * Get sorted options
     *
     * @param BucketInterface $bucket
     * @param array $optionLabels
     * @param string $attributeType
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getSortedOptions(BucketInterface $bucket, array $optionLabels, string $attributeType): array
    {
        /**
         * Option labels array has been sorted
         */
        $options = $optionLabels;
        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $optionValue = $metrics['value'];
            if (isset($optionLabels[$optionValue])) {
                $optionLabel = $optionLabels[$optionValue];
            } else {
                if ($attributeType === 'boolean') {
                    $yesNoOptions = $this->YesNo->toArray();
                    $optionLabel = $yesNoOptions[$optionValue];
                } else {
                    $optionLabel =  $optionValue;
                }
            }
            $options[$optionValue] = $metrics + ['label' => $optionLabel];
        }

        /**
         * Delete options without bucket values
         */
        foreach ($options as $optionId => $option) {
            if (!is_array($options[$optionId])) {
                unset($options[$optionId]);
            }
        }

        return array_values($options);
    }
}
