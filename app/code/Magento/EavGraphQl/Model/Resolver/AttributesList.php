<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Output\GetAttributeDataInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns a list of attributes metadata for a given entity type.
 */
class AttributesList implements ResolverInterface
{
    /**
     * @var GetAttributeDataInterface
     */
    private GetAttributeDataInterface $getAttributeData;

    /**
     * @var EnumLookup
     */
    private EnumLookup $enumLookup;

    /**
     * @var GetFilteredAttributes
     */
    private GetFilteredAttributes $getFilteredAttributes;

    /**
     * @param EnumLookup                $enumLookup
     * @param GetAttributeDataInterface $getAttributeData
     * @param GetFilteredAttributes     $getFilteredAttributes
     */
    public function __construct(
        EnumLookup $enumLookup,
        GetAttributeDataInterface $getAttributeData,
        GetFilteredAttributes $getFilteredAttributes,
    ) {
        $this->enumLookup = $enumLookup;
        $this->getAttributeData = $getAttributeData;
        $this->getFilteredAttributes = $getFilteredAttributes;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (!$args['entityType']) {
            throw new GraphQlInputException(__('Required parameter "%1" of type string.', 'entityType'));
        }

        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $entityType = $this->enumLookup->getEnumValueFromField(
            'AttributeEntityTypeEnum',
            strtolower($args['entityType'])
        );

        $filterArgs = $args['filters'] ?? [];

        $attributesList = $this->getFilteredAttributes->execute($filterArgs, strtolower($entityType));

        return [
            'items' => $this->getAttributesMetadata($attributesList['items'], $entityType, $storeId),
            'entity_type' => $entityType,
            'errors' => $attributesList['errors']
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
