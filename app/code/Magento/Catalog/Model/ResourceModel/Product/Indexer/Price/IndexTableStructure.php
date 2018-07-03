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
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $entityField;

    /**
     * @var string
     */
    private $customerGroupField;

    /**
     * @var string
     */
    private $websiteField;

    /**
     * @var string
     */
    private $taxClassField;

    /**
     * @var string
     */
    private $originalPriceField;

    /**
     * @var string
     */
    private $finalPriceField;

    /**
     * @var string
     */
    private $minPriceField;

    /**
     * @var string
     */
    private $maxPriceField;

    /**
     * @var string
     */
    private $tierPriceField;

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
        $this->tableName = $tableName;
        $this->entityField = $entityField;
        $this->customerGroupField = $customerGroupField;
        $this->websiteField = $websiteField;
        $this->taxClassField = $taxClassField;
        $this->originalPriceField = $originalPriceField;
        $this->finalPriceField = $finalPriceField;
        $this->minPriceField = $minPriceField;
        $this->maxPriceField = $maxPriceField;
        $this->tierPriceField = $tierPriceField;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getEntityField(): string
    {
        return $this->entityField;
    }

    /**
     * @return string
     */
    public function getCustomerGroupField(): string
    {
        return $this->customerGroupField;
    }

    /**
     * @return string
     */
    public function getWebsiteField(): string
    {
        return $this->websiteField;
    }

    /**
     * @return string
     */
    public function getTaxClassField(): string
    {
        return $this->taxClassField;
    }

    /**
     * @return string
     */
    public function getOriginalPriceField(): string
    {
        return $this->originalPriceField;
    }

    /**
     * @return string
     */
    public function getFinalPriceField(): string
    {
        return $this->finalPriceField;
    }

    /**
     * @return string
     */
    public function getMinPriceField(): string
    {
        return $this->minPriceField;
    }

    /**
     * @return string
     */
    public function getMaxPriceField(): string
    {
        return $this->maxPriceField;
    }

    /**
     * @return string
     */
    public function getTierPriceField(): string
    {
        return $this->tierPriceField;
    }
}
