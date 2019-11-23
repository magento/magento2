<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\Query;

/**
 * Category filter allows to filter collection using 'id, url_key, name' from search criteria.
 */
class CategoryFilter
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Filter for filtering the requested categories id's based on url_key, ids, name in the result.
     *
     * @param array $args
     * @param Collection $categoryCollection
     * @param StoreInterface $store
     * @throws InputException
     */
    public function applyFilters(array $args, Collection $categoryCollection, StoreInterface $store)
    {
        $categoryCollection->addAttributeToFilter(CategoryInterface::KEY_IS_ACTIVE, ['eq' => 1]);
        foreach ($args['filters'] as $field => $cond) {
            foreach ($cond as $condType => $value) {
                if ($field === 'ids') {
                    $categoryCollection->addIdFilter($value);
                } else {
                    $this->addAttributeFilter($categoryCollection, $field, $condType, $value, $store);
                }
            }
        }
    }

    /**
     * Add filter to category collection
     *
     * @param Collection $categoryCollection
     * @param string $field
     * @param string $condType
     * @param string|array $value
     * @param StoreInterface $store
     * @throws InputException
     */
    private function addAttributeFilter($categoryCollection, $field, $condType, $value, $store)
    {
        if ($condType === 'match') {
            $this->addMatchFilter($categoryCollection, $field, $value, $store);
            return;
        }
        $categoryCollection->addAttributeToFilter($field, [$condType => $value]);
    }

    /**
     * Add match filter to collection
     *
     * @param Collection $categoryCollection
     * @param string $field
     * @param string $value
     * @param StoreInterface $store
     * @throws InputException
     */
    private function addMatchFilter($categoryCollection, $field, $value, $store)
    {
        $minQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $searchValue = str_replace('%', '', $value);
        $matchLength = strlen($searchValue);
        if ($matchLength < $minQueryLength) {
            throw new InputException(__('Invalid match filter'));
        }

        $categoryCollection->addAttributeToFilter($field, ['like' => "%{$searchValue}%"]);
    }
}
