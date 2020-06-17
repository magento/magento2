<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\Filter;

/**
 * Order filter allows to filter collection using 'increment_id' as order number, from the search criteria.
 */
class OrderFilter
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Translator field from graphql to collection field
     *
     * @var string[]
     */
    private $fieldTranslatorArray = [
        'number' => 'increment_id',
    ];

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param string[] $fieldTranslatorArray
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        array $fieldTranslatorArray = []
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->fieldTranslatorArray = array_replace($this->fieldTranslatorArray, $fieldTranslatorArray);
    }

    /**
     * Filter for filtering the requested categories id's based on url_key, ids, name in the result.
     *
     * @param int $userId
     * @param array $args
     * @param StoreInterface $store
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function applyFilter(
        int $userId,
        array $args,
        StoreInterface $store,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ): void {
        $filterGroups = [];
        $this->filterGroupBuilder->setFilters(
            [$this->filterBuilder->setField('customer_id')->setValue($userId)->setConditionType('eq')->create()]
        );
        $filterGroups[] = $this->filterGroupBuilder->create();

        $this->filterGroupBuilder->setFilters(
            [$this->filterBuilder->setField('store_id')->setValue($store->getId())->setConditionType('eq')->create()]
        );
        $filterGroups[] = $this->filterGroupBuilder->create();

        if (isset($args['filter'])) {
            $filters = [];
            foreach ($args['filter'] as $field => $cond) {
                if (isset($this->fieldTranslatorArray[$field])) {
                    $field = $this->fieldTranslatorArray[$field];
                }
                foreach ($cond as $condType => $value) {
                    if ($condType === 'match') {
                        if (is_array($value)) {
                            throw new InputException(__('Invalid match filter'));
                        }
                        $searchValue = str_replace('%', '', $value);
                        $filters[] = $this->filterBuilder->setField($field)
                            ->setValue("%{$searchValue}%")
                            ->setConditionType('like')
                            ->create();
                    } else {
                        $filters[] = $this->filterBuilder->setField($field)
                            ->setValue($value)
                            ->setConditionType($condType)
                            ->create();
                    }
                }
            }

            $this->filterGroupBuilder->setFilters($filters);
            $filterGroups[] = $this->filterGroupBuilder->create();
        }
        $searchCriteriaBuilder->setFilterGroups($filterGroups);
    }
}
