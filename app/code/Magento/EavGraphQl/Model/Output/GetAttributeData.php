<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\EavGraphQl\Model\Uid as AttributeUid;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Format attributes for GraphQL output
 */
class GetAttributeData implements GetAttributeDataInterface
{
    /**
     * @var AttributeUid
     */
    private AttributeUid $attributeUid;

    /**
     * @var Uid
     */
    private Uid $uid;

    /**
     * @var EnumLookup
     */
    private EnumLookup $enumLookup;

    /**
     * @param AttributeUid $attributeUid
     * @param Uid $uid
     * @param EnumLookup $enumLookup
     */
    public function __construct(AttributeUid $attributeUid, Uid $uid, EnumLookup $enumLookup)
    {
        $this->attributeUid = $attributeUid;
        $this->uid = $uid;
        $this->enumLookup = $enumLookup;
    }

    /**
     * Retrieve formatted attribute data
     *
     * @param AttributeInterface $attribute
     * @param string $entityType
     * @param int $storeId
     * @return array
     * @throws RuntimeException
     */
    public function execute(
        AttributeInterface $attribute,
        string $entityType,
        int $storeId
    ): array {
        return [
            'uid' => $this->attributeUid->encode($entityType, $attribute->getAttributeCode()),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel($storeId),
            'sort_order' => $attribute->getPosition(),
            'entity_type' => $this->enumLookup->getEnumValueFromField(
                'AttributeEntityTypeEnum',
                $entityType
            ),
            'frontend_input' => $this->getFrontendInput($attribute),
            'is_required' => $attribute->getIsRequired(),
            'default_value' => $attribute->getDefaultValue(),
            'is_unique' => $attribute->getIsUnique(),
            'options' => $this->getOptions($attribute),
            'attribute' => $attribute
        ];
    }

    /**
     * Returns default frontend input for attribute if not set
     *
     * @param AttributeInterface $attribute
     * @return string
     */
    private function getFrontendInput(AttributeInterface $attribute): string
    {
        if ($attribute->getFrontendInput() === null) {
            return "UNDEFINED";
        }
        return $this->enumLookup->getEnumValueFromField(
            'AttributeFrontendInputEnum',
            $attribute->getFrontendInput()
        );
    }

    /**
     * Retrieve formatted attribute options
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function getOptions(AttributeInterface $attribute): array
    {
        if (!$attribute->getOptions()) {
            return [];
        }
        return array_filter(
            array_map(
                function (AttributeOptionInterface $option) use ($attribute) {
                    if (is_array($option->getValue())) {
                        $value =  (empty($option->getValue()) ? '' : (string)$option->getValue()[0]['value']);
                    } else {
                        $value = (string)$option->getValue();
                    }
                    $label = (string)$option->getLabel();
                    if (empty(trim($value)) && empty(trim($label))) {
                        return null;
                    }
                    return [
                        'uid' => $this->uid->encode($value),
                        'label' => $label,
                        'value' => $value,
                        'is_default' => $attribute->getDefaultValue() ?
                            in_array($value, explode(',', $attribute->getDefaultValue())) : null
                    ];
                },
                $attribute->getOptions()
            )
        );
    }
}
