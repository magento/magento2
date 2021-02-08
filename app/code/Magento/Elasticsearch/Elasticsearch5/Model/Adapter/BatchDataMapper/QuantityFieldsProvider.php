<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\BatchDataMapper;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;

/**
 * Provide data mapping for quantity_and_stock_status field.
 */
class QuantityFieldsProvider implements AdditionalFieldsProviderInterface
{
    private const ATTRIBUTE_CODE = 'quantity_and_stock_status';

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @param AttributeProvider $attributeAdapterProvider
     * @param StockStateInterface $stockState
     */
    public function __construct(AttributeProvider $attributeAdapterProvider, StockStateInterface $stockState)
    {
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->stockState = $stockState;
    }

    /**
     * Get quantity_and_stock_status fields for data mapper
     *
     * @param array $productIds
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFields(array $productIds, $storeId)
    {
        $fields = [];
        $attribute = $this->attributeAdapterProvider->getByAttributeCode(self::ATTRIBUTE_CODE);
        if ($attribute->isSortable()) {
            foreach ($productIds as $productId) {
                $fields[$productId][self::ATTRIBUTE_CODE] = $this->stockState->getStockQty($productId);
            }
        }

        return $fields;
    }
}
