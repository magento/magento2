<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Observer is responsible for changing scope for all price attributes in system
 * depending on 'Catalog Price Scope' configuration parameter
 */
class SwitchPriceAttributeScopeOnConfigChange implements ObserverInterface
{
    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ReinitableConfigInterface $config
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ReinitableConfigInterface $config,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->config = $config;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Change scope for all price attributes according to
     * 'Catalog Price Scope' configuration parameter value
     *
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        $this->searchCriteriaBuilder->addFilter('frontend_input', 'price');
        $criteria = $this->searchCriteriaBuilder->create();

        $scope = $this->config->getValue(Store::XML_PATH_PRICE_SCOPE);
        $scope = ($scope == Store::PRICE_SCOPE_WEBSITE)
            ? ProductAttributeInterface::SCOPE_WEBSITE_TEXT
            : ProductAttributeInterface::SCOPE_GLOBAL_TEXT;

        $priceAttributes = $this->productAttributeRepository->getList($criteria)->getItems();

        /** @var ProductAttributeInterface $priceAttribute */
        foreach ($priceAttributes as $priceAttribute) {
            $priceAttribute->setScope($scope);
            $this->productAttributeRepository->save($priceAttribute);
        }
    }
}
