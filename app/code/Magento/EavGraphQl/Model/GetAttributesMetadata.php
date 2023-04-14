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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Retrieve EAV attributes details
 */
class GetAttributesMetadata
{
    /**
     * @var Uid
     */
    private Uid $uid;

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
     * @param Uid $uid
     * @param GetAttributeDataInterface $getAttributeData
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        Uid $uid,
        GetAttributeDataInterface $getAttributeData
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->uid = $uid;
        $this->getAttributeData = $getAttributeData;
    }

    /**
     * Get attribute metadata details
     *
     * @param string[] $uids
     * @param int $storeId
     * @return array
     * @throws RuntimeException
     */
    public function execute(array $uids, int $storeId): array
    {
        if (empty($uids)) {
            return [];
        }

        $codes = [];
        $errors = [];

        foreach ($uids as $uid) {
            try {
                list($entityType, $attributeCode) = $this->uid->decode($uid);
                $codes[$entityType][] = $attributeCode;
            } catch (GraphQlInputException $exception) {
                $errors[] = [
                    'type' => 'INCORRECT_UID',
                    'message' => $exception->getMessage()
                ];
            }
        }

        $items = [];

        foreach ($codes as $entityType => $attributeCodes) {
            $builder = $this->searchCriteriaBuilderFactory->create();
            $builder->addFilter('attribute_code', $attributeCodes, 'in');
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
