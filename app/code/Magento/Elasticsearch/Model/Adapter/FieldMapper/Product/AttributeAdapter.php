<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Product attribute adapter for elasticsearch context.
 */
class AttributeAdapter
{
    /**
     * @var AbstractExtensibleModel
     */
    private $attribute;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @param AbstractExtensibleModel $attribute
     * @param string $attributeCode
     */
    public function __construct(
        AbstractExtensibleModel $attribute,
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
     * Check if attribute is defined by user.
     *
     * @return string
     */
    public function isUserDefined(): string
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
     * @return AbstractExtensibleModel|\Magento\Eav\Api\Data\AttributeInterface
     */
    private function getAttribute(): AbstractExtensibleModel
    {
        return $this->attribute;
    }
}
