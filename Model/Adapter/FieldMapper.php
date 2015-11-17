<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Model\Session as CustomerSession;

/**
 * Class FieldMapper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FieldMapper implements FieldMapperInterface
{
    const ES_DATA_TYPE_STRING = 'string';
    const ES_DATA_TYPE_FLOAT = 'float';
    const ES_DATA_TYPE_INT = 'integer';
    const ES_DATA_TYPE_DATE = 'date';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Config $eavConfig
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ResolverInterface $localeResolver
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Config $eavConfig,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $localeResolver,
        CustomerSession $customerSession
    ) {
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getFieldName($attributeCode, $context = [])
    {
        if (in_array($attributeCode, ['id', 'sku', 'store_id', 'category_ids'], true)) {
            return $attributeCode;
        }

        if ($attributeCode === 'price') {
            return $this->getPriceFieldName($context);
        }
        if ($attributeCode === 'position') {
            return $this->getPositionFiledName($context);
        }

        $storeId = !empty($context['storeId'])
            ? $context['storeId']
            : $this->storeManager->getStore()->getId();

        $fieldName = $attributeCode . '_' . $storeId;

        return $fieldName;
    }

    /**
     * Gives all mapped attribute types
     *
     * @return array
     */
    public function getAllAttributesTypes()
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $stores = $this->storeManager->getStores();
        $allAttributes = [];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
            $notUsedInSearch = [];
            if ($this->isAttributeUsedInAdvancedSearch($attribute) === false) {
                $notUsedInSearch = ['index' => 'no'];
            }
            foreach ($stores as $store) {
                $attributeCodeByStore = $attribute->getIsGlobal() ? $attributeCode : $attributeCode.'_'.$store->getId();
                $allAttributes[$attributeCodeByStore] = [
                    'type' => $this->getFieldType($attribute)
                ];
                if ($notUsedInSearch) {
                    $allAttributes[$attributeCodeByStore] = array_merge(
                        $allAttributes[$attributeCodeByStore],
                        $notUsedInSearch
                    );
                }
            }
        }
        return $allAttributes;
    }

    /**
     * @param Object $attribute
     * @return bool
     */
    private function isAttributeUsedInAdvancedSearch($attribute)
    {
        return $attribute->getIsVisibleInAdvancedSearch()
        || $attribute->getIsFilterable()
        || $attribute->getIsFilterableInSearch();
    }

    /**
     * Get "position" field name
     *
     * @param array $context
     * @return string
     */
    private function getPositionFiledName($context)
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
    private function getPriceFieldName($context)
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();
        $websiteId = !empty($context['websiteId'])
            ? $context['websiteId']
            : $this->storeManager->getStore()->getWebsiteId();
        return 'price_' . $customerGroupId . '_' . $websiteId;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    private function getFieldType($attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ($backendType === 'timestamp' || $backendType === 'datetime') {
            $fieldType = self::ES_DATA_TYPE_DATE;
        } elseif ($backendType === 'int' || $backendType === 'smallint') {
            $fieldType = self::ES_DATA_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ES_DATA_TYPE_FLOAT;
        } elseif ($backendType === 'varchar') {
            $fieldType = self::ES_DATA_TYPE_STRING;
        } elseif (in_array($frontendInput, ['multiselect', 'select', 'boolean'], true)) {
            $fieldType = self::ES_DATA_TYPE_INT;
        } else {
            $fieldType = self::ES_DATA_TYPE_STRING;
        }
        return $fieldType;
    }
}
