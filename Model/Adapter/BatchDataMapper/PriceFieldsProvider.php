<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

/**
 * Provide data mapping for price fields
 */
class PriceFieldsProvider implements AdditionalFieldsProviderInterface
{
    /**
     * @var Index
     */
    private $resourceIndex;

    /**
     * @var AttributeContainer
     */
    private $attributeContainer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Index $resourceIndex
     * @param AttributeContainer $attributeContainer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Index $resourceIndex,
        AttributeContainer $attributeContainer,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceIndex = $resourceIndex;
        $this->attributeContainer = $attributeContainer;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(array $productIds, $storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $priceData = $this->attributeContainer->getSearchableAttribute('price')
            ? $this->resourceIndex->getPriceIndexData($productIds, $websiteId)
            : [];

        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductPriceData($productId, $storeId, $priceData);
        }

        return $fields;
    }

    /**
     * Prepare price index for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param array $priceIndexData
     * @return array
     */
    private function getProductPriceData($productId, $websiteId, array $priceIndexData)
    {
        $result = [];
        if (array_key_exists($productId, $priceIndexData)) {
            $productPriceIndexData = $priceIndexData[$productId];
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = 'price_' . $customerGroupId . '_' . $websiteId;
                $result[$fieldName] = sprintf('%F', $price);
            }
        }

        return $result;
    }
}
