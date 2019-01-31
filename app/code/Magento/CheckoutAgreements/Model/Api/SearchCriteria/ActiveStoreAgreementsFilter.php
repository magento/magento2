<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model\Api\SearchCriteria;

/**
 * Build search criteria for agreements list.
 */
class ActiveStoreAgreementsFilter
{
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Build search criteria with store and is_active filters.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    public function buildSearchCriteria() : \Magento\Framework\Api\SearchCriteriaInterface
    {
        $storeFilter = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('eq')
            ->setValue($this->storeManager->getStore()->getId())
            ->create();
        $isActiveFilter = $this->filterBuilder->setField('is_active')
            ->setConditionType('eq')
            ->setValue(1)
            ->create();
        $this->searchCriteriaBuilder->addFilters([$storeFilter]);
        $this->searchCriteriaBuilder->addFilters([$isActiveFilter]);
        return $this->searchCriteriaBuilder->create();
    }
}
