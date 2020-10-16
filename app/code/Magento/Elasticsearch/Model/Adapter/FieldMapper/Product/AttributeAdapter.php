<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Product attribute adapter for elasticsearch context.
 */
class AttributeAdapter
{
    /**
     * @var CustomAttributesDataInterface
     */
    private $attribute;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @param CustomAttributesDataInterface $attribute
     * @param string $attributeCode
     */
    public function __construct(
        CustomAttributesDataInterface $attribute,
        string $attributeCode
    ) {
        $this->attribute = $attribute;
        $this->attributeCode = $attributeCode;
    }

    /**
     * Check if attribute is filterable.
     *
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->getAttribute()->getIsFilterable() || $this->getAttribute()->getIsFilterableInSearch();
    }

    /**
     * Check if attribute is searchable.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->getAttribute()->getIsSearchable()
            || ($this->getAttribute()->getIsVisibleInAdvancedSearch()
                || $this->isFilterable());
    }

    /**
     * Check if attribute is need to index always.
     *
     * @return bool
     */
    public function isAlwaysIndexable(): bool
    {
        // List of attributes which are required to be indexable
        $alwaysIndexableAttributes = [
            'category_ids',
            'visibility',
        ];

        return in_array($this->getAttributeCode(), $alwaysIndexableAttributes, true);
    }

    /**
     * Check if attribute has date/time type.
     *
     * @return bool
     */
    public function isDateTimeType(): bool
    {
        return in_array($this->getAttribute()->getBackendType(), ['timestamp', 'datetime'], true);
    }

    /**
     * Check if attribute has float type.
     *
     * @return bool
     */
    public function isFloatType(): bool
    {
        return $this->getAttribute()->getBackendType() === 'decimal';
    }

    /**
     * Check if attribute has integer type.
     *
     * @return bool
     */
    public function isIntegerType(): bool
    {
        return in_array($this->getAttribute()->getBackendType(), ['int', 'smallint'], true);
    }

    /**
     * Check if attribute has boolean type.
     *
     * @return bool
     */
    public function isBooleanType(): bool
    {
        return in_array($this->getAttribute()->getFrontendInput(), ['select', 'boolean'], true)
            && $this->getAttribute()->getBackendType() !== 'varchar';
    }

    /**
     * Check if attribute is text type
     *
     * @return bool
     */
    public function isTextType(): bool
    {
        return in_array($this->getAttribute()->getBackendType(), ['varchar', 'static'], true)
            && in_array($this->getFrontendInput(), ['text'], true)
            && $this->getAttribute()->getIsVisible();
    }

    /**
     * Check if attribute has boolean type.
     *
     * @return bool
     */
    public function isComplexType(): bool
    {
        return in_array($this->getAttribute()->getFrontendInput(), ['select', 'multiselect'], true)
            || $this->getAttribute()->usesSource();
    }

    /**
     * Check if product attribute is EAV.
     *
     * @return bool
     */
    public function isEavAttribute(): bool
    {
        return $this->getAttribute() instanceof \Magento\Eav\Api\Data\AttributeInterface;
    }

    /**
     * Get attribute code.
     *
     * @return string
     */
    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    /**
     * Check if attribute is sortable.
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return (int)$this->getAttribute()->getUsedForSortBy() === 1;
    }

    /**
     * Check if attribute is defined by user.
     *
     * @return bool|null
     */
    public function isUserDefined()
    {
        return $this->getAttribute()->getIsUserDefined();
    }

    /**
     * Frontend HTML for input element.
     *
     * @return string
     */
    public function getFrontendInput()
    {
        return $this->getAttribute()->getFrontendInput();
    }

    /**
     * Get product attribute instance.
     *
     * @return CustomAttributesDataInterface|\Magento\Eav\Api\Data\AttributeInterface
     */
    private function getAttribute(): CustomAttributesDataInterface
    {
        return $this->attribute;
    }
}
