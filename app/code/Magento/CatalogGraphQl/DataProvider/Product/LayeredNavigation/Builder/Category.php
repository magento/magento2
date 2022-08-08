<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\DataProvider\Category\Query\CategoryAttributeQuery;
use Magento\CatalogGraphQl\DataProvider\CategoryAttributesMapper;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilderInterface;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\RootCategoryProvider;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Category layer builder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category implements LayerBuilderInterface
{
    /**
     * @var string
     */
    private const CATEGORY_BUCKET = 'category_bucket';

    /**
     * @var array
     */
    private static $bucketMap = [
        self::CATEGORY_BUCKET => [
            'request_name' => 'category_uid',
            'label' => 'Category'
        ],
    ];

    /**
     * @var CategoryAttributeQuery
     */
    private CategoryAttributeQuery $categoryAttributeQuery;

    /**
     * @var CategoryAttributesMapper
     */
    private CategoryAttributesMapper $attributesMapper;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var RootCategoryProvider
     */
    private RootCategoryProvider $rootCategoryProvider;

    /**
     * @var LayerFormatter
     */
    private LayerFormatter $layerFormatter;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $categoryCollectionFactory;

    /**
     * @var Aggregations\Category\IncludeDirectChildrenOnly
     */
    private Aggregations\Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly;

    /**
     * @param CategoryAttributeQuery $categoryAttributeQuery
     * @param CategoryAttributesMapper $attributesMapper
     * @param RootCategoryProvider $rootCategoryProvider
     * @param ResourceConnection $resourceConnection
     * @param LayerFormatter $layerFormatter
     * @param Aggregations\Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CategoryAttributeQuery $categoryAttributeQuery,
        CategoryAttributesMapper $attributesMapper,
        RootCategoryProvider $rootCategoryProvider,
        ResourceConnection $resourceConnection,
        LayerFormatter $layerFormatter,
        Aggregations\Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryAttributeQuery = $categoryAttributeQuery;
        $this->attributesMapper = $attributesMapper;
        $this->resourceConnection = $resourceConnection;
        $this->rootCategoryProvider = $rootCategoryProvider;
        $this->layerFormatter = $layerFormatter;
        $this->includeDirectChildrenOnly = $includeDirectChildrenOnly;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $aggregation = $this->includeDirectChildrenOnly->filter($aggregation, $storeId);
        $bucket = $aggregation->getBucket(self::CATEGORY_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }

        $categoryIds = \array_map(
            function (AggregationValueInterface $value) {
                return (int)$value->getValue();
            },
            $bucket->getValues()
        );

        if ($storeId) {
            $storeFilteredCategoryIds = $this->getStoreCategoryIds($storeId);
            $categoryIds = \array_intersect($categoryIds, $storeFilteredCategoryIds);
        }

        $categoryIds = \array_diff($categoryIds, [$this->rootCategoryProvider->getRootCategory($storeId)]);
        $categoryLabels = \array_column(
            $this->attributesMapper->getAttributesValues(
                $this->resourceConnection->getConnection()->fetchAll(
                    $this->categoryAttributeQuery->getQuery($categoryIds, ['name'], $storeId)
                )
            ),
            'name',
            'entity_id'
        );

        if (!$categoryLabels) {
            return [];
        }

        $result = $this->layerFormatter->buildLayer(
            self::$bucketMap[self::CATEGORY_BUCKET]['label'],
            \count($categoryIds),
            self::$bucketMap[self::CATEGORY_BUCKET]['request_name']
        );

        foreach ($bucket->getValues() as $value) {
            $categoryId = $value->getValue();
            if (!\in_array($categoryId, $categoryIds, true)) {
                continue;
            }
            $result['options'][] = $this->layerFormatter->buildItem(
                $categoryLabels[$categoryId] ?? $categoryId,
                $categoryId,
                $value->getMetrics()['count']
            );
        }

        return [$result];
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
     * List of store categories
     *
     * @param int $storeId
     * @return array
     */
    private function getStoreCategoryIds(int $storeId): array
    {
        $storeRootCategoryId = $this->rootCategoryProvider->getRootCategory($storeId);
        $collection = $this->categoryCollectionFactory->create();
        $select = $collection->getSelect();
        $connection = $collection->getConnection();
        $select->where(
            $connection->quoteInto(
                'e.path LIKE ? OR e.entity_id=' . $connection->quote($storeRootCategoryId, 'int'),
                '%/' . $storeRootCategoryId . '/%'
            )
        );
        return $collection->getAllIds();
    }
}
