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
use Magento\EavGraphQl\Model\Output\GetAttributeData;
use Magento\EavGraphQl\Model\Output\GetAttributeDataInterface;

/**
 * Resolve attribute options data for custom attribute.
 */
class AttributesList implements ResolverInterface
{
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @var GetAttributeDataInterface
     */
    private GetAttributeDataInterface $getAttributeData;

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
     * @param GetAttributeDataInterface $getAttributeData
     * @param array $resolvers
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EnumLookup $enumLookup,
        GetAttributeDataInterface $getAttributeData,
        array $resolvers = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->enumLookup = $enumLookup;
        $this->getAttributeData = $getAttributeData;
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
    ): array {
        if (!$args['entity_type']) {
            throw new GraphQlInputException(__('Required parameter "%1" of type string.', 'entity_type'));
        }

        $errors = [];
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
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
            'items' => $this->getAtrributesMetadata($attributesList, $entityType, $storeId),
            'errors' => $errors
        ];
    }

    /**
     * Returns formatted list of attributes
     *
     * @param array $attributesList
     * @param string $entityType
     * @param int $storeId
     *
     * @return array
     */
    private function getAtrributesMetadata(array $attributesList, string $entityType, int $storeId)
    {
        return array_map(function ($attribute) use ($entityType, $storeId) {
            return $this->getAttributeData->execute($attribute, mb_strtolower($entityType), $storeId);
        }, $attributesList);
    }
}
