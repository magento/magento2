<?php

namespace Magento\Catalog\Model\Plugin\ProductRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\StoreManagerInterface;

class BeforeProductRepositoryGetList
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var FilterGroupBuilder  */
    private $filterGroupBuilder;

    /** @var FilterBuilder  */
    private $filterBuilder;

    /**
     * BeforeProductRepositoryGetList constructor.
     * @param StoreManagerInterface $storeManager
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Append a store filter at the end of search criteria.
     *
     * @param ProductRepositoryInterface $subject
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
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
