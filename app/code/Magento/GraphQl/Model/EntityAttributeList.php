<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model;

use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Iterate through all attribute sets to retrieve attributes for any given entity type
 */
class EntityAttributeList
{
    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param AttributeManagementInterface $attributeManagement
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        AttributeManagementInterface $attributeManagement,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->attributeManagement =  $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Retrieve all EAV and custom attribute codes from all attribute sets for given entity code.
     *
     * Returned in the format [$attributeCode => $isSortable] with $isSortable being a boolean value where an attribute
     * can be sorted with in a search criteria expression. The metadata service parameter is only required if type has
     * custom attributes.
     *
     * @param string $entityCode
     * @param MetadataServiceInterface $metadataService
     * @return boolean[]
     * @throws GraphQlNoSuchEntityException
     */
    public function getDefaultEntityAttributes(
        string $entityCode,
        ?MetadataServiceInterface $metadataService = null
    ) : array {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('entity_type_code')
                    ->setValue($entityCode)
                    ->setConditionType('eq')
                    ->create(),
            ]
        );
        $attributeSetList = $this->attributeSetRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $attributes = [];
        foreach ($attributeSetList as $attributeSet) {
            try {
                $attributes = array_merge(
                    $attributes,
                    $this->attributeManagement->getAttributes($entityCode, $attributeSet->getAttributeSetId())
                );
            } catch (NoSuchEntityException $exception) {
                throw new GraphQlNoSuchEntityException(__('Entity code %1 does not exist.', [$entityCode]));
            }
        }
        $attributeCodes = [];
        $metadata = $metadataService ? $metadataService->getCustomAttributesMetadata() : [];
        foreach ($metadata as $customAttribute) {
            if (!array_key_exists($customAttribute->getAttributeCode(), $attributeCodes)) {
                $attributeCodes[$customAttribute->getAttributeCode()] = false;
            }
        }
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            if (!array_key_exists($attribute->getAttributeCode(), $attributeCodes)) {
                $attributeCodes[$attribute->getAttributeCode()]
                    = ((! $attribute->getIsUserDefined()) && !is_array($attribute));
            }
        }
        return $attributeCodes;
    }
}
