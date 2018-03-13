<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Wrapper for structure of price index table.
 */
class IndexTableStructure
{
    /**
     * @var array
     */
    private $structure;

    /**
     * @param string $tableName
     * @param string $entityField
     * @param string $customerGroupField
     * @param string $websiteField
     * @param string $taxClassField
     * @param string $originalPriceField
     * @param string $finalPriceField
     * @param string $minPriceField
     * @param string $maxPriceField
     * @param string $tierPriceField
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $tableName,
        string $entityField,
        string $customerGroupField,
        string $websiteField,
        string $taxClassField,
        string $originalPriceField,
        string $finalPriceField,
        string $minPriceField,
        string $maxPriceField,
        string $tierPriceField
    ) {
        $this->structure = [
            'table_name' => $tableName,
            'entity_field' => $entityField,
            'customer_group_field' => $customerGroupField,
            'website_field' => $websiteField,
            'tax_class_field' => $taxClassField,
            'original_price_field' => $originalPriceField,
            'final_price_field' => $finalPriceField,
            'min_price_field' => $minPriceField,
            'max_price_field' => $maxPriceField,
            'tier_price_field' => $tierPriceField,
        ];
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->structure['table_name'];
    }

    /**
     * @return string
     */
    public function getEntityField(): string
    {
        return $this->structure['entity_field'];
    }

    /**
     * @return string
     */
    public function getCustomerGroupField(): string
    {
        return $this->structure['customer_group_field'];
    }

    /**
     * @return string
     */
    public function getWebsiteField(): string
    {
        return $this->structure['website_field'];
    }

    /**
     * @return string
     */
    public function getTaxClassField(): string
    {
        return $this->structure['tax_class_field'];
    }

    /**
     * @return string
     */
    public function getOriginalPriceField(): string
    {
        return $this->structure['original_price_field'];
    }

    /**
     * @return string
     */
    public function getFinalPriceField(): string
    {
        return $this->structure['final_price_field'];
    }

    /**
     * @return string
     */
    public function getMinPriceField(): string
    {
        return $this->structure['min_price_field'];
    }

    /**
     * @return string
     */
    public function getMaxPriceField(): string
    {
        return $this->structure['max_price_field'];
    }

    /**
     * @return string
     */
    public function getTierPriceField(): string
    {
        return $this->structure['tier_price_field'];
    }
}
