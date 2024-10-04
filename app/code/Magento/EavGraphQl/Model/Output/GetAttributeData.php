<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Query\EnumLookup;

/**
 * Format attributes for GraphQL output
 */
class GetAttributeData implements GetAttributeDataInterface
{
    /**
     * @var EnumLookup
     */
    private EnumLookup $enumLookup;

    /**
     * @var array
     */
    private array $skipOptionsForAttributeCodes;

    /**
     * @param EnumLookup $enumLookup
     * @param array $skipOptionsForAttributeCodes
     */
    public function __construct(EnumLookup $enumLookup, array $skipOptionsForAttributeCodes = [])
    {
        $this->enumLookup = $enumLookup;
        $this->skipOptionsForAttributeCodes = $skipOptionsForAttributeCodes;
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
            'id' => $attribute->getAttributeId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel($storeId),
            'sort_order' => $attribute->getPosition(),
            'entity_type' => $this->enumLookup->getEnumValueFromField(
                'AttributeEntityTypeEnum',
                $entityType
            ),
            'frontend_input' => $this->getFrontendInput($attribute),
            'frontend_class' => $attribute->getFrontendClass(),
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
     * @throws RuntimeException
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
        if (!$attribute->getOptions() || $this->skipOptions($attribute)) {
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
                        'label' => $label,
                        'value' => $value,
                        'is_default' => $attribute->getDefaultValue() &&
                            $this->isDefault($value, $attribute->getDefaultValue())
                    ];
                },
                $attribute->getOptions()
            )
        );
    }

    /**
     * Returns true if $value is the default value. Otherwise, false.
     *
     * @param mixed $value
     * @param mixed $defaultValue
     * @return bool
     */
    private function isDefault(mixed $value, mixed $defaultValue): bool
    {
        if (is_array($defaultValue)) {
            return in_array($value, $defaultValue);
        }

        return in_array($value, explode(',', $defaultValue));
    }

    /**
     * Skip attributes options for region_id and country_id
     *
     * Attributes region_id and country_id returns large datasets that is also not related between each other and
     * not filterable. DirectoryGraphQl contains queries that allow to retrieve this information in a meaningful way
     *
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function skipOptions(AttributeInterface $attribute): bool
    {
        return in_array($attribute->getAttributeCode(), $this->skipOptionsForAttributeCodes);
    }
}
