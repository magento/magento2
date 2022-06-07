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
use Magento\Elasticsearch\Model\ResourceModel\StockInventory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide data mapping for price fields
 */
class IsOutOfStockFieldsProvider implements AdditionalFieldsProviderInterface
{
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
     * @var StockInventory
     */
    private $stockInventory;


    /**
     * @param StoreManagerInterface $storeManager
     * @param AttributeProvider $attributeAdapterProvider
     * @param ResolverInterface $fieldNameResolver
     * @param StockInventory $stockInventory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AttributeProvider $attributeAdapterProvider,
        ResolverInterface $fieldNameResolver,
        StockInventory $stockInventory
    ) {
        $this->storeManager = $storeManager;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->stockInventory = $stockInventory;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFields(array $productIds, $storeId): array
    {
        $this->stockInventory->saveRelation($productIds);

        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductStockData($productId, $websiteId, $storeId);
        }

        $this->stockInventory->clearRelation();

        return $fields;
    }

    /**
     * Prepare saleability status for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function getProductStockData($productId, $websiteId, $storeId): array
    {
        $result = [];
        $saleabilityAttribute = $this->attributeAdapterProvider->getByAttributeCode('is_out_of_stock');
        $fieldName = $this->fieldNameResolver->getFieldName(
            $saleabilityAttribute,
            ['websiteId' => $websiteId]
        );

        $sku = $this->stockInventory->getSkuRelation($productId);
        if (!$sku) {
            return ['is_out_of_stock' => 1];
        }
        $value = $this->stockInventory->getStockStatus(
            $sku,
            $this->storeManager->getStore($storeId)->getWebsite()->getCode()
        );

        $result[$fieldName] = $value;

        return $result;
    }
}
