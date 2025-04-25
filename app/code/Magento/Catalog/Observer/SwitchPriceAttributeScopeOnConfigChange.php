<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Config;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store;

class PriceScopeChange
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Updates the price attributes scope
     *
     * @param int $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     *
     * @retrun void
     */
    public function changeScope(int $value): void
    {
        $this->searchCriteriaBuilder->addFilter('frontend_input', 'price');
        $criteria = $this->searchCriteriaBuilder->create();

        $scope = ($value === Store::PRICE_SCOPE_WEBSITE)
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
