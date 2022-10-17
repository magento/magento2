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
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

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
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

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
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        LayerFormatter $layerFormatter,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->layerFormatter = $layerFormatter;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $storeFrontLabel = '';

        $attribute = $this->attributeRepository->get(
            self::$bucketMap[self::PRICE_BUCKET]['request_name']
        );

        if ($attribute) {
            $storeFrontLabel =  isset($attribute->getStorelabels()[$storeId]) ?
                $attribute->getStorelabels()[$storeId] : $attribute->getFrontendLabel();
        }

        $bucket = $aggregation->getBucket(self::PRICE_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $result = $this->layerFormatter->buildLayer(
            $storeFrontLabel,
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
}
