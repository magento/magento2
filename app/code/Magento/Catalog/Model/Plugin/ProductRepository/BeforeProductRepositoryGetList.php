<?php

namespace Magento\Catalog\Model\Plugin\ProductRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\StoreManagerInterface;

class BeforeProductRepositoryGetList
{
    private $storeManager;
    private $filterGroupBuilder;
    private $filterBuilder;

    public function __construct(
        StoreManagerInterface $storeManager,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    public function beforeGetList(ProductRepositoryInterface $subject, SearchCriteriaInterface $searchCriteria)
    {
        $filterGroups = $searchCriteria->getFilterGroups();
        $storeFilter = $this->filterBuilder->setField('store_id')
            ->setConditionType('eq')
            ->setValue($this->storeManager->getStore()->getId())
            ->create();
        $filterGroups[] = $this->filterGroupBuilder->addFilter($storeFilter)->create();
        $searchCriteria->setFilterGroups($filterGroups);

        return [$searchCriteria];
    }
}
