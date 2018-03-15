<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\DataObject;

/**
 * Wrapper for structure of price index table.
 *
 * @method string getTableName()
 * @method string getEntityField()
 * @method string getCustomerGroupField()
 * @method string getWebsiteField()
 * @method string getTaxClassField()
 * @method string getOriginalPriceField()
 * @method string getFinalPriceField()
 * @method string getMinPriceField()
 * @method string getMaxPriceField()
 * @method string getTierPriceField()
 */
class IndexTableStructure extends DataObject
{
    /**
     * @inheritdoc
     */
    public function __construct(array $data = [])
    {
        $requiredFields = [
            'table_name',
            'entity_field',
            'customer_group_field',
            'website_field',
            'tax_class_field',
            'original_price_field',
            'final_price_field',
            'min_price_field',
            'max_price_field',
            'tier_price_field',
        ];
        $data = array_filter($data);
        $emptyRequiredFields = array_diff_key(array_flip($requiredFields), $data);
        if ($emptyRequiredFields) {
            throw new \InvalidArgumentException(
                '[' . implode(', ', array_keys($emptyRequiredFields)) . '] are required fields'
            );
        }

        parent::__construct($data);
    }
}
