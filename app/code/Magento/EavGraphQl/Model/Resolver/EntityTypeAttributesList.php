<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
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
     * @var array
     */
    private array $resolvers;

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
            throw new GraphQlInputException(__('Required parameter "%1" of type string.', 'entity_type'));
        }

        $entityType = $this->enumLookup->getEnumValueFromField(
            'AttributeEntityTypeEnum',
            mb_strtolower($args['entity_type'])
        );

        $searchCriteria = $this->searchCriteriaBuilder;

        foreach ($this->resolvers as $resolver) {
            $searchCriteria->addFilter($resolver['name'], $resolver['object']->execute());
        }
        $searchCriteria = $searchCriteria->create();

        $attributesList = $this->attributeRepository->getList(mb_strtolower($entityType), $searchCriteria)->getItems();

        return [
            'items' => $this->getAtrributesMetadata($attributesList),
            'errors' => $errors
        ];
    }

    /**
     * Returns formatted list of attributes
     *
     * @param array $attributesList
     * @return array
     */
    private function getAtrributesMetadata($attributesList)
    {
        return array_map(function ($attribute) {
            return [
                'uid' => $attribute->getAttributeId(),
                'attribute_code' => $attribute->getData('attribute_code'),
                'frontend_label' => $attribute->getData('frontend_label'),
                'entity_type_id' => $attribute->getData('entity_type_id'),
                'frontend_input' => $attribute->getData('frontend_input'),
                'is_required' => $attribute->getData('is_required'),
                'default_value' => $attribute->getData('default_value'),
                'is_unique' => $attribute->getIsUnique(),
                'options' => $attribute->getData('options')
            ];
        }, $attributesList);
    }
}
