<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Entity;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\GetCustomAttributeCodesInterface;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class GetProductCustomAttributeCodes implements GetCustomAttributeCodesInterface
{
    /**
     * @var GetCustomAttributeCodesInterface
     */
    private $baseCustomAttributeCodes;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var array[]
     */
    private $customAttributeCodes = [];

    /**
     * @param GetCustomAttributeCodesInterface    $baseCustomAttributeCodes
     * @param SearchCriteriaBuilder               $searchCriteriaBuilder
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        GetCustomAttributeCodesInterface $baseCustomAttributeCodes,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->baseCustomAttributeCodes = $baseCustomAttributeCodes;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(MetadataServiceInterface $metadataService, ?int $attributeSetId = null): array
    {
        if (null !== $attributeSetId) {
            return $this->getAttributesForSet($attributeSetId);
        }

        return $this->getAttributes($metadataService);
    }

    private function getAttributesForSet(int $attributeSetId)
    {
        if (!isset($this->customAttributeCodes[$attributeSetId])) {
            $codes = [];
            $criteria = $this->searchCriteriaBuilder->addFilter('attribute_set_id', $attributeSetId, 'eq');
            $attributes = $this->attributeRepository->getList($criteria->create())->getItems();

            if (is_array($attributes)) {
                /** @var $attribute \Magento\Framework\Api\MetadataObjectInterface */
                foreach ($attributes as $attribute) {
                    $codes[] = $attribute->getAttributeCode();
                }
            }

            $codes = array_values(
                array_diff($codes, ProductInterface::ATTRIBUTES)
            );

            $this->customAttributeCodes[$attributeSetId] = $codes;
        }

        return $this->customAttributeCodes[$attributeSetId];
    }

    private function getAttributes(MetadataServiceInterface $metadataService)
    {
        $customAttributesCodes = $this->baseCustomAttributeCodes->execute($metadataService);

        return array_diff($customAttributesCodes, ProductInterface::ATTRIBUTES);
    }
}
