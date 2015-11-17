<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapper;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class DocumentDataMapper
{
    /**
     * Array of \DateTime objects per store
     *
     * @var \DateTime[]
     */
    protected $dateFormats = [];

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var AttributeContainer
     */
    private $attributeContainer;

    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Builder $builder
     * @param AttributeContainer $attributeContainer
     * @param FieldMapper $fieldMapper
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Builder $builder,
        AttributeContainer $attributeContainer,
        FieldMapper $fieldMapper,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->builder = $builder;
        $this->attributeContainer = $attributeContainer;
        $this->fieldMapper = $fieldMapper;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @param array $productIndexData
     * @param int $productId
     * @param int $storeId
     * @param array $productPriceIndexData
     * @param array $productCategoryIndexData
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function map(
        array $productIndexData,
        $productId,
        $storeId,
        array $productPriceIndexData,
        array $productCategoryIndexData
    ) {
        $this->builder->addField('store_id', $storeId);
        $store = ['storeId' => $storeId];
        foreach ($productIndexData as $attributeCode => $value) {
            // Prepare processing attribute info
            /* @var Attribute|null $attribute */
            $attribute = $this->attributeContainer->getAttribute($attributeCode);
            if (!$attribute || $attributeCode === 'price') {
                continue;
            }

            $attribute->setStoreId($storeId);
            if (is_array($value)) {
                $value = array_shift($value);
            }

            if ($attribute->getBackendType() === 'datetime' || $attribute->getBackendType() === 'timestamp'
                || $attribute->getFrontendInput() === 'date') {
                $value = $this->formatDate($storeId, $value);
            }

            $this->builder->addField($this->fieldMapper->getFieldName($attributeCode, $store), $value);

            unset($attribute);
        }

        $this->builder->addFields($this->getProductPriceData($productId, $storeId, $productPriceIndexData));
        $this->builder->addFields($this->getProductCategoryData($productId, $productCategoryIndexData));

        return $this->builder->build();
    }

    /**
     * Implode index array to string by separator
     *
     * @param array $indexData
     * @param string $separator
     * @return string
     */
    protected function implodeIndexData($indexData, $separator = ' ')
    {
        $result = [];

        foreach ((array)$indexData as $value) {
            $result[] = $value;
        }
        $result = array_unique($result);

        return implode($separator, $result);
    }

    /**
     * Prepare price index for product
     *
     * @param int $productId
     * @param int $storeId
     * @param array $priceIndexData
     * @return array
     */
    protected function getProductPriceData($productId, $storeId, array $priceIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $priceIndexData)) {
            $productPriceIndexData = $priceIndexData[$productId];

            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = $this->getPriceFieldName($customerGroupId, $websiteId);
                $result[$fieldName] = sprintf('%F', $price);
            }
        }

        return $result;
    }

    /**
     * Retrieve date value in elasticseacrh format (ISO 8601) with Z
     * Example: 1995-12-31T23:59:59Z
     *
     * @param int $storeId
     * @param string|null $date
     * @return string|null
     */
    protected function formatDate($storeId, $date = null)
    {
        if ($this->dateTime->isEmptyDate($date)) {
            return null;
        }

        if (!array_key_exists($storeId, $this->dateFormats)) {
            $timezone = $this->scopeConfig->getValue(
                $this->localeDate->getDefaultTimezonePath(),
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $dateObj = new \DateTime();
            $dateObj->setTimezone(new \DateTimeZone($timezone));
            $this->dateFormats[$storeId] = $dateObj;
        }

        $dateObj = $this->dateFormats[$storeId];
        return $dateObj->format('c') . 'Z';
    }

    /**
     * Prepare category index data for product
     *
     * @param int $productId
     * @param array $categoryIndexData
     * @return array
     */
    protected function getProductCategoryData($productId, array $categoryIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];

            $categoryIds = array_keys($indexData);
            if (count($categoryIds)) {
                $ids = $this->implodeIndexData($categoryIds, ',');
                $result = ['category_ids' => $ids];

                foreach ($indexData as $categoryId => $position) {
                    $result['position_category_' . $categoryId] = $position;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare price field name for search engine
     *
     * @param null|int $customerGroupId
     * @param null|int $websiteId
     * @return string
     */
    protected function getPriceFieldName($customerGroupId = null, $websiteId = null)
    {
        $context = [];
        if ($customerGroupId !== null) {
            $context['customerGroupId'] = $customerGroupId;
        }
        if ($websiteId !== null) {
            $context['websiteId'] = $websiteId;
        }

        return $this->fieldMapper->getFieldName('price', $context);
    }
}
