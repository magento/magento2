<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide data mapping for inventory fields
 */
class InventoryFieldsProvider implements AdditionalFieldsProviderInterface
{
    public const IS_SALABLE = 'is_salable';

    /**
     * @var Index
     */
    private $resourceIndex;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Index $resourceIndex
     * @param StoreManagerInterface $storeManager
     * @param AttributeProvider $attributeAdapterProvider
     * @param ResolverInterface $fieldNameResolver
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Index $resourceIndex,
        StoreManagerInterface $storeManager,
        AttributeProvider $attributeAdapterProvider,
        ResolverInterface $fieldNameResolver,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceIndex = $resourceIndex;
        $this->storeManager = $storeManager;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        $fields = [];

        if ($this->hasShowOutOfStockStatus()) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $inventoryData = $this->resourceIndex->getInventoryIndexData($productIds);
            foreach ($productIds as $productId) {
                $fields[$productId] = $this->getProductInventoryData($productId, $websiteId, $inventoryData);
            }
        }
        return $fields;
    }

    /**
     * Prepare inventory index for product.
     *
     * @param int $productId
     * @param int $websiteId
     * @param array $inventoryData
     * @return array
     */
    private function getProductInventoryData($productId, $websiteId, array $inventoryData)
    {
        $result = [];
        if (array_key_exists($productId, $inventoryData)) {
            $inStockAttribute = $this->attributeAdapterProvider->getByAttributeCode(self::IS_SALABLE);
            $fieldName = $this->fieldNameResolver->getFieldName(
                $inStockAttribute,
                ['websiteId' => $websiteId]
            );
            $result[$fieldName] = $inventoryData[$productId];
        }

        return $result;
    }

    /**
     * Returns if display out of stock status set or not in catalog inventory
     *
     * @return bool
     */
    private function hasShowOutOfStockStatus(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
