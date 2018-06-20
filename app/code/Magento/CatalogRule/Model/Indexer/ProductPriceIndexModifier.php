<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price;

/**
 * Class for adding catalog rule prices to price index table.
 */
class ProductPriceIndexModifier implements PriceModifierInterface
{
    /**
     * @var Price
     */
    private $priceResourceModel;

    /**
     * @param Price $priceResourceModel
     */
    public function __construct(Price $priceResourceModel)
    {
        $this->priceResourceModel = $priceResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = [])
    {
        $connection = $this->priceResourceModel->getConnection();
        $select = $connection->select();

        $select->join(
            ['cpiw' => $this->priceResourceModel->getTable('catalog_product_index_website')],
            'cpiw.website_id = i.' . $priceTable->getWebsiteField(),
            []
        );
        $select->join(
            ['cpp' => $this->priceResourceModel->getMainTable()],
            'cpp.product_id = i.' . $priceTable->getEntityField()
            . ' AND cpp.customer_group_id = i.' . $priceTable->getCustomerGroupField()
            . ' AND cpp.website_id = i.' . $priceTable->getWebsiteField()
            . ' AND cpp.rule_date = cpiw.website_date',
            []
        );
        if ($entityIds) {
            $select->where('i.entity_id IN (?)', $entityIds);
        }

        $finalPrice = $priceTable->getFinalPriceField();
        $finalPriceExpr = $select->getConnection()->getLeastSql([
            $priceTable->getFinalPriceField(),
            $select->getConnection()->getIfNullSql('cpp.rule_price', 'i.' . $finalPrice),
        ]);
        $minPrice = $priceTable->getMinPriceField();
        $minPriceExpr = $select->getConnection()->getLeastSql([
            $priceTable->getMinPriceField(),
            $select->getConnection()->getIfNullSql('cpp.rule_price', 'i.' . $minPrice),
        ]);
        $select->columns([
            $finalPrice => $finalPriceExpr,
            $minPrice => $minPriceExpr,
        ]);

        $query = $connection->updateFromSelect($select, ['i' => $priceTable->getTableName()]);
        $connection->query($query);
    }
}
