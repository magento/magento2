<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use \Magento\Customer\Model\Session as CustomerSession;

/**
 * Class ProductFieldMapper
 */
class ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var FieldType
     */
    protected $fieldType;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Config $eavConfig
     * @param FieldType $fieldType
     * @param CustomerSession $customerSession
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType,
        CustomerSession $customerSession,
        StoreManager $storeManager,
        Registry $coreRegistry
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        if (!$attribute || in_array($attributeCode, ['id', 'sku', 'store_id', 'visibility'], true)) {
            return $attributeCode;
        }
        if ($attributeCode === 'price') {
            return $this->getPriceFieldName($context);
        }
        if ($attributeCode === 'position') {
            return $this->getPositionFiledName($context);
        }
        $fieldType = $this->fieldType->getFieldType($attribute);
        $frontendInput = $attribute->getFrontendInput();
        if (empty($context['type'])) {
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_FILTER) {
            if ($fieldType === 'string') {
                return $this->getFieldName(
                    $attributeCode,
                    array_merge($context, ['type' => FieldMapperInterface::TYPE_QUERY])
                );
            }
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_QUERY) {
            $fieldName = $this->getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode);
        } else {
            $fieldName = 'sort_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributesTypes($context = [])
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];
        // List of attributes which are required to be indexable
        $alwaysIndexableAttributes = [
            'media_gallery',
            'quantity_and_stock_status',
            'tier_price',
            'category_ids',
            'visibility',
        ];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);

            $allAttributes[$attributeCode] = [
                'type' => $this->fieldType->getFieldType($attribute)
            ];

            if (!$attribute->getIsSearchable() && !$this->isAttributeUsedInAdvancedSearch($attribute)
                && !in_array($attributeCode, $alwaysIndexableAttributes, true)
            ) {
                $allAttributes[$attributeCode] = array_merge(
                    $allAttributes[$attributeCode],
                    ['index' => 'no']
                );
            }

            if ($attribute->getFrontendInput() === 'select') {
                $allAttributes[$attributeCode . '_value'] = [
                    'type' => FieldType::ES_DATA_TYPE_STRING,
                ];
            }
        }

        return $allAttributes;
    }

    /**
     * @param Object $attribute
     * @return bool
     */
    protected function isAttributeUsedInAdvancedSearch($attribute)
    {
        return $attribute->getIsVisibleInAdvancedSearch()
        || $attribute->getIsFilterable()
        || $attribute->getIsFilterableInSearch();
    }

    /**
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    protected function getRefinedFieldName($frontendInput, $fieldType, $attributeCode)
    {
        return (in_array($frontendInput, ['select', 'boolean'], true) && $fieldType === 'integer')
            ? $attributeCode . '_value' : $attributeCode;
    }

    /**
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    protected function getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode)
    {
        if ($attributeCode === '*') {
            $fieldName = '_all';
        } else {
            $fieldName = $this->getRefinedFieldName($frontendInput, $fieldType, $attributeCode);
        }
        return $fieldName;
    }

    /**
     * Get "position" field name
     *
     * @param array $context
     * @return string
     */
    protected function getPositionFiledName($context)
    {
        if (isset($context['categoryId'])) {
            $category = $context['categoryId'];
        } else {
            $category = $this->coreRegistry->registry('current_category')
                ? $this->coreRegistry->registry('current_category')->getId()
                : $this->storeManager->getStore()->getRootCategoryId();
        }
        return 'position_category_' . $category;
    }

    /**
     * Prepare price field name for search engine
     *
     * @param array $context
     * @return string
     */
    protected function getPriceFieldName($context)
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();
        $websiteId = !empty($context['websiteId'])
            ? $context['websiteId']
            : $this->storeManager->getStore()->getWebsiteId();
        return 'price_' . $customerGroupId . '_' . $websiteId;
    }
}
