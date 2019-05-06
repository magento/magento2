<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerMetadataManagementInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Get allowed address attributes
 */
class GetAllowedCustomerAttributes
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get allowed customer attributes
     *
     * @param array $attributeKeys
     * @throws GraphQlInputException
     * @return AbstractAttribute[]
     */
    public function execute($attributeKeys): array
    {
        $this->searchCriteriaBuilder->addFilter('attribute_code', $attributeKeys, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        try {
            $attributesSearchResult = $this->attributeRepository->getList(
                CustomerMetadataManagementInterface::ENTITY_TYPE_CUSTOMER,
                $searchCriteria
            );
        } catch (InputException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        /** @var AbstractAttribute[] $attributes */
        $attributes = $attributesSearchResult->getItems();

        foreach ($attributes as $index => $attribute) {
            if (false === $attribute->getIsVisibleOnFront()) {
                unset($attributes[$index]);
            }
        }

        return $attributes;
    }
}
