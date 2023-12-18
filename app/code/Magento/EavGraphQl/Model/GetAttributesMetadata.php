<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Output\GetAttributeDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Retrieve EAV attributes details
 */
class GetAttributesMetadata
{
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    /**
     * @var GetAttributeDataInterface
     */
    private GetAttributeDataInterface $getAttributeData;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param GetAttributeDataInterface $getAttributeData
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        GetAttributeDataInterface $getAttributeData
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->getAttributeData = $getAttributeData;
    }

    /**
     * Get attribute metadata details
     *
     * @param array $attributesInputs
     * @param int $storeId
     * @return array
     * @throws RuntimeException
     */
    public function execute(array $attributesInputs, int $storeId): array
    {
        if (empty($attributesInputs)) {
            return [];
        }

        $codes = [];
        $errors = [];

        foreach ($attributesInputs as $attributeInput) {
            $codes[$attributeInput['entity_type']][] = $attributeInput['attribute_code'];
        }

        $items = [];

        foreach ($codes as $entityType => $attributeCodes) {
            $builder = $this->searchCriteriaBuilderFactory->create();
            $builder
                ->addFilter('attribute_code', $attributeCodes, 'in');
            try {
                $attributes = $this->attributeRepository->getList($entityType, $builder->create())->getItems();
            } catch (LocalizedException $exception) {
                $errors[] = [
                    'type' => 'ENTITY_NOT_FOUND',
                    'message' => (string) __('Entity "%entity" could not be found.', ['entity' => $entityType])
                ];
                continue;
            }

            $notFoundCodes = array_diff($attributeCodes, $this->getCodes($attributes));
            foreach ($notFoundCodes as $notFoundCode) {
                $errors[] = [
                    'type' => 'ATTRIBUTE_NOT_FOUND',
                    'message' => (string) __('Attribute code "%code" could not be found.', ['code' => $notFoundCode])
                ];
            }
            foreach ($attributes as $attribute) {
                if (method_exists($attribute, 'getIsVisible') && !$attribute->getIsVisible()) {
                    continue;
                }
                $items[] = $this->getAttributeData->execute($attribute, $entityType, $storeId);
            }
        }

        return [
            'items' => $items,
            'errors' => $errors
        ];
    }

    /**
     * Retrieve an array of codes from the array of attributes
     *
     * @param AttributeInterface[] $attributes
     * @return AttributeInterface[]
     */
    private function getCodes(array $attributes): array
    {
        return array_map(
            function (AttributeInterface $attribute) {
                return $attribute->getAttributeCode();
            },
            $attributes
        );
    }
}
