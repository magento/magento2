<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\RuntimeException;
use Magento\EavGraphQl\Model\Output\GetAttributeDataInterface;

/**
 * Returns a list of attributes metadata for a given entity type.
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
    private array $searchCriteriaProviders;

    /**
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EnumLookup $enumLookup
     * @param GetAttributeDataInterface $getAttributeData
     * @param array $searchCriteriaProviders
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EnumLookup $enumLookup,
        GetAttributeDataInterface $getAttributeData,
        array $searchCriteriaProviders = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->enumLookup = $enumLookup;
        $this->getAttributeData = $getAttributeData;
        $this->searchCriteriaProviders = $searchCriteriaProviders;
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
        if (!$args['entityType']) {
            throw new GraphQlInputException(__('Required parameter "%1" of type string.', 'entityType'));
        }

        $errors = [];
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $entityType = $this->enumLookup->getEnumValueFromField(
            'AttributeEntityTypeEnum',
            strtolower($args['entityType'])
        );

        $searchCriteria = $this->searchCriteriaBuilder;
        foreach ($this->searchCriteriaProviders as $key => $provider) {
            if (!$provider instanceof ResolverInterface) {
                throw new RuntimeException(
                    __('Configured search criteria provider should implement ResolverInterface')
                );
            }
            $searchCriteria->addFilter($key, $provider->resolve($field, $context, $info));
        }
        $searchCriteria = $searchCriteria->addFilter("is_visible", true)->create();

        $attributesList = $this->attributeRepository->getList(strtolower($entityType), $searchCriteria)->getItems();
        return [
            'items' => $this->getAttributesMetadata($attributesList, $entityType, $storeId),
            'errors' => $errors
        ];
    }

    /**
     * Returns formatted list of attributes
     *
     * @param AttributeInterface[] $attributesList
     * @param string $entityType
     * @param int $storeId
     *
     * @return array[]
     * @throws RuntimeException
     */
    private function getAttributesMetadata(array $attributesList, string $entityType, int $storeId): array
    {
        return array_map(function (AttributeInterface $attribute) use ($entityType, $storeId): array {
            return $this->getAttributeData->execute($attribute, strtolower($entityType), $storeId);
        }, $attributesList);
    }
}
