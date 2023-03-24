<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Phrase;

/**
 * Resolve attribute options data for custom attribute.
 */
class EntityTypeAttributesList implements ResolverInterface
{
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var EnumLookup
     */
    private EnumLookup $enumLookup;

    /**
     * array
     */
    private $resolvers;

    /**
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EnumLookup $enumLookup
     * @param array $resolvers
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EnumLookup $enumLookup,
        array $resolvers = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->enumLookup = $enumLookup;
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): mixed {
        $errors = [];

        if (!$args['entity_type']) {
            throw new GraphQlInputException(__("Missing rquired 'entity_type' argument"));
        }

        $entityType = $this->enumLookup->getEnumValueFromField(
            'AttributeEntityTypeEnum',
            $args['entity_type']
        );

        // $entityType = 'catalog_product';
        $searchCriteria = $this->searchCriteriaBuilder;

        foreach ($this->resolvers as $resolver) {
            $searchCriteria->addFilter($resolver['name'], $resolver['object']->resolve());
        }
        $searchCriteria = $searchCriteria->create();

        $attributesList = $this->attributeRepository->getList($entityType, $searchCriteria)->getItems();

        return [
            'items' => $this->getAtrributesMetadata($attributesList),
            'errors' => $errors
        ];
    }

    private function getAtrributesMetadata($attributesList)
    {
        return array_map(function ($attribute) {
            return [
                'uid' => $attribute->getAttributeId(),
                'attribute_id' => $attribute->getAttributeId(),
                'is_unique' => $attribute->getIsUnique(),
                'scope' => $attribute->getData('scope'),
                'frontend_class' => $attribute->getData('frontend_class'),
                'frontend_input' => $attribute->getData('frontend_input'),
                'attribute_code' => $attribute->getData('attribute_code'),
                'is_required' => $attribute->getData('is_required'),
                'options' => $attribute->getData('options'),
                'is_user_defined' => $attribute->getData('is_user_defined'),
                'frontend_label' => $attribute->getData('frontend_label'),
                'note' => $attribute->getData('note'),
                'frontend_labels' => $attribute->getData('frontend_labels'),
                'backend_type' => $attribute->getData('backend_type'),
                'source_model' => $attribute->getData('source_model'),
                'backend_model' => $attribute->getData('backend_model'),
                'validate_rules' => $attribute->getData('validate_rules'),
                'entity_type_id' => $attribute->getData('entity_type_id'),
                'code' => $attribute->getAttributeCode(),
                'label' => $attribute->getDefaultFrontendLabel()
            ];
        }, $attributesList);
    }
}
