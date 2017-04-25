<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query;

use Magento\Framework\Search\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\CatalogSearch\Model\Adapter\Aggregation\RequestCheckerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Request checker for catalog view.
 *
 * Checks catalog view query whether required to collect all attributes for entity.
 */
class CatalogView implements RequestCheckerInterface
{
    /**
     * Identifier for query name
     */
    private $name;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param string $name
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        $name
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(RequestInterface $request)
    {
        $result = true;
        if ($request->getName() === $this->name) {
            $result = $this->hasAnchorCategory($request);
        }

        return $result;
    }

    /**
     * Check whether category is anchor.
     *
     * Proceeds with request and check whether at least one of categories is anchor.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function hasAnchorCategory(RequestInterface $request)
    {
        $queryType = $request->getQuery()->getType();
        $result = false;

        if ($queryType === QueryInterface::TYPE_BOOL) {
            $categories = $this->getCategoriesFromQuery($request->getQuery());

            /** @var \Magento\Catalog\Api\Data\CategoryInterface $category */
            foreach ($categories as $category) {
                // It's no need to render LN filters for non anchor categories
                if ($category && $category->getIsAnchor()) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get categories based on query filter data.
     *
     * Get categories from query will allow to check if category is anchor
     * And proceed with attribute aggregation if it's not
     *
     * @param QueryInterface $queryExpression
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]|[]
     */
    private function getCategoriesFromQuery(QueryInterface $queryExpression)
    {
        /** @var BoolExpression $queryExpression */
        $categoryIds = $this->getCategoryIdsFromQuery($queryExpression);
        $categories = [];

        foreach ($categoryIds as $categoryId) {
            try {
                $categories[] = $this->categoryRepository
                    ->get($categoryId, $this->storeManager->getStore()->getId());
            } catch (NoSuchEntityException $e) {
                // do nothing if category is not found by id
            }
        }

        return $categories;
    }

    /**
     * Get Category Ids from search query.
     *
     * Get Category Ids from Must and Should search queries.
     *
     * @param QueryInterface $queryExpression
     * @return array
     */
    private function getCategoryIdsFromQuery(QueryInterface $queryExpression)
    {
        $queryFilterArray = [];
        /** @var BoolExpression $queryExpression */
        $queryFilterArray[] = $queryExpression->getMust();
        $queryFilterArray[] = $queryExpression->getShould();
        $categoryIds = [];

        foreach ($queryFilterArray as $item) {
            if (!empty($item) && isset($item['category'])) {
                $queryFilter = $item['category'];
                /** @var \Magento\Framework\Search\Request\Query\Filter $queryFilter */
                $categoryIds[] = $queryFilter->getReference()->getValue();
            }
        }

        return $categoryIds;
    }
}
