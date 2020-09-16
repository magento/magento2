<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Add price data to product collection results
 */
class PriceDataProcessor implements CollectionProcessorInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Process to add price data to product collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {
        try {
            if ($context
                && $context->getExtensionAttributes()->getIsCustomer() === true
            ) {
                $customer = $this->customerRepository->getById(
                    $context->getUserId()
                );
                $customerGroupId = (int) $customer->getGroupId();
            } else {
                $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
            }
        } catch (Exception $e) {
            $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
        }
        $collection->addPriceData($customerGroupId);

        return $collection;
    }
}
