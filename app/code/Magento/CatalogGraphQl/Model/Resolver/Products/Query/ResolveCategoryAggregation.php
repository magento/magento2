<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Search\Response\Aggregation;
use Magento\Framework\Search\Response\AggregationFactory;
use Magento\Framework\Search\Response\Bucket;
use Magento\Framework\Search\Response\BucketFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolve category aggregation.
 */
class ResolveCategoryAggregation
{
    /**
     * @var string
     */
    public const CATEGORY_BUCKET = 'category_bucket';

    /**
     * @var string
     */
    private const BUCKETS = 'buckets';

    /**
     * @var AggregationFactory
     */
    private $aggregationFactory;

    /**
     * @var BucketFactory
     */
    private $bucketFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $resolvedChildrenIds = [];

    /**
     * @param AggregationFactory $aggregationFactory
     * @param BucketFactory $bucketFactory
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        AggregationFactory $aggregationFactory,
        BucketFactory $bucketFactory,
        StoreManagerInterface $storeManager,
        CategoryRepository $categoryRepository
    ) {
        $this->aggregationFactory = $aggregationFactory;
        $this->bucketFactory = $bucketFactory;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get resolved category aggregation.
     *
     * @param array $categoryFilter
     * @param Bucket[] $bucketList
     *
     * @return Aggregation
     */
    public function getResolvedCategoryAggregation(array $categoryFilter, array $bucketList): Aggregation
    {
        $categoryBucket = $bucketList[self::CATEGORY_BUCKET] ?? [];
        $values = $categoryBucket->getValues();
        $condition = array_key_first($categoryFilter);
        $searchableCategoryValue = $categoryFilter[$condition] ?? 0;

        if (!$searchableCategoryValue || empty($values)) {
            return $this->aggregationFactory->create([self::BUCKETS => $bucketList]);
        }

        $resolvedBucketList = $bucketList;

        try {
            if (!is_array($searchableCategoryValue)) {
                $resolvedValues = $this->getValidCategories((int) $searchableCategoryValue, $values);
            } else {
                $categoryResolvedValues = [];

                foreach ($searchableCategoryValue as $categoryId) {
                    $categoryResolvedValues[] = $this->getValidCategories((int) $categoryId, $values);
                }

                $resolvedValues = call_user_func_array('array_merge', $categoryResolvedValues);;
            }
        } catch (NoSuchEntityException $e) {
            return $this->aggregationFactory->create([self::BUCKETS => $bucketList]);
        }

        $resolvedCategoryBucket = $this->bucketFactory->create(
            [
                'name' => self::CATEGORY_BUCKET,
                'values' => $resolvedValues
            ]
        );

        $resolvedBucketList[self::CATEGORY_BUCKET] = $resolvedCategoryBucket;

        return $this->aggregationFactory->create([self::BUCKETS => $resolvedBucketList]);
    }

    /**
     * Check is valid searchable category children.
     *
     * @param int $searchableCategoryId
     * @param AggregationValueInterface[] $aggregationValues
     *
     * @return AggregationValueInterface[]
     *
     * @throws NoSuchEntityException
     */
    private function getValidCategories(int $searchableCategoryId, array $aggregationValues): array
    {
        $stareId = (int) $this->storeManager->getStore()->getId();
        $searchableCategory = $this->categoryRepository->get($searchableCategoryId, $stareId);
        $childrenList = $searchableCategory->getChildrenCategories();
        $resolvedList = [];
        $validChildIdList = [];

        foreach ($childrenList as $child) {
            if (!$child->getIsActive()) {
                continue;
            }

            $validChildIdList[] = $child->getId();
        }

        foreach ($aggregationValues as $bucketValue) {
            $childCategoryId = (int) $bucketValue->getValue();

            if (!in_array($childCategoryId, $validChildIdList) ||
                in_array($childCategoryId, $this->resolvedChildrenIds)
            ) {
                continue;
            }

            $resolvedList[] = $bucketValue;
            $this->resolvedChildrenIds[] = $childCategoryId;
        }

        return $resolvedList;
    }
}
