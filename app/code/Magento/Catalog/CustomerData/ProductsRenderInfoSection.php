<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\CustomerData;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Api\Data\ProductFrontendActionInterface;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Catalog\Model\ProductRenderList;
use Magento\Catalog\Model\ResourceModel\ProductFrontendAction\Collection;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\Hydrator;
use Magento\Store\Model\StoreManager;

/**
 * Section which provide information about products to customer data (private cache storage)
 */
class ProductsRenderInfoSection implements SectionSourceInterface
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ProductRenderList
     */
    private $productRenderList;

    /**
     * @var Synchronizer
     */
    private $actionsSynchronizer;

    /**
     * ProductsRenderInfoSection constructor.
     * @param StoreManager $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductRenderList $productRenderList
     * @param Synchronizer $actionsSynchronizer
     * @param Hydrator $hydrator
     */
    public function __construct(
        StoreManager $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductRenderList $productRenderList,
        Synchronizer $actionsSynchronizer,
        Hydrator $hydrator
    ) {
        $this->storeManager = $storeManager;
        $this->hydrator = $hydrator;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->productRenderList = $productRenderList;
        $this->actionsSynchronizer = $actionsSynchronizer;
    }

    /**
     * Aggregate by actions (recently_viewed or recently_compared) desired product ids
     *
     * @return array
     */
    private function getProductIds()
    {
        $productIds = [];
        /** @var Collection $actionCollection */
        $actionCollection = $this->actionsSynchronizer->getAllActions();

        /** @var ProductFrontendActionInterface $action */
        foreach ($actionCollection as $action) {
            $productIds[] = $action->getProductId();
        }

        return $productIds;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $sectionData = [];
        $store = $this->storeManager->getStore();
        $filter = $this->filterBuilder
            ->setField('entity_id')
            ->setValue($this->getProductIds())
            ->setConditionType('in')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$filter])
            ->create();
        $renderSearchResults = $this->productRenderList->getList(
            $searchCriteria,
            $store->getId(),
            $store->getCurrentCurrencyCode()
        );

        /** @var ProductRenderInterface $item */
        foreach ($renderSearchResults->getItems() as $item) {
            $sectionData[$item->getId()] = $this->hydrator->extract($item);
        }

        return $sectionData;
    }
}
