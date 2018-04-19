<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Bundle products Price indexer resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
{
    /**
     * @inheritdoc
     */
    protected function reindex($entityIds = null)
    {
        $this->_prepareBundlePrice($entityIds);
    }

    /**
     * Retrieve temporary price index table name for fixed bundle products
     *
     * @return string
     */
    protected function _getBundlePriceTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_bundle');
    }

    /**
     * Retrieve table name for temporary bundle selection prices index
     *
     * @return string
     */
    protected function _getBundleSelectionTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_bundle_sel');
    }

    /**
     * Retrieve table name for temporary bundle option prices index
     *
     * @return string
     */
    protected function _getBundleOptionTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_bundle_opt');
    }

    /**
     * Prepare temporary price index table for fixed bundle products
     *
     * @return $this
     */
    protected function _prepareBundlePriceTable()
    {
        $this->getConnection()->delete($this->_getBundlePriceTable());
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle selection prices index
     *
     * @return $this
     */
    protected function _prepareBundleSelectionTable()
    {
        $this->getConnection()->delete($this->_getBundleSelectionTable());
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle option prices index
     *
     * @return $this
     */
    protected function _prepareBundleOptionTable()
    {
        $this->getConnection()->delete($this->_getBundleOptionTable());
        return $this;
    }

    /**
     * Prepare temporary price index data for bundle products by price type
     *
     * @param int $priceType
     * @param int|array $entityIds the entity ids limitation
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareBundlePriceByType($priceType, $entityIds = null)
    {
        $connection = $this->getConnection();
        $table = $this->_getBundlePriceTable();

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', "e.entity_id");
        $select->columns(
            'website_id',
            'cw'
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->joinLeft(
            ['tp' => $this->_getTierPriceIndexTable()],
            'tp.entity_id = e.entity_id AND tp.website_id = cw.website_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        );

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $this->_addAttributeToSelect($select, 'status', "e.$linkField", 'cs.store_id', $statusCond, true);
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', "e.$linkField", 'cs.store_id');
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $select->columns(['tax_class_id' => new \Zend_Db_Expr('0')]);
        } else {
            $select->columns(
                ['tax_class_id' => $connection->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)]
            );
        }

        $priceTypeCond = $connection->quoteInto('=?', $priceType);
        $this->_addAttributeToSelect($select, 'price_type', "e.$linkField", 'cs.store_id', $priceTypeCond);

        $price = $this->_addAttributeToSelect($select, 'price', "e.$linkField", 'cs.store_id');
        $specialPrice = $this->_addAttributeToSelect($select, 'special_price', "e.$linkField", 'cs.store_id');
        $specialFrom = $this->_addAttributeToSelect($select, 'special_from_date', "e.$linkField", 'cs.store_id');
        $specialTo = $this->_addAttributeToSelect($select, 'special_to_date', "e.$linkField", 'cs.store_id');
        $currentDate = new \Zend_Db_Expr('cwd.website_date');

        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialExpr = "{$specialPrice} IS NOT NULL AND {$specialPrice} > 0 AND {$specialPrice} < 100"
            . " AND {$specialFromExpr} AND {$specialToExpr}";
        $tierExpr = new \Zend_Db_Expr('tp.min_price');

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $specialPriceExpr = $connection->getCheckSql(
                $specialExpr,
                'ROUND(' . $price . ' * (' . $specialPrice . '  / 100), 4)',
                'NULL'
            );
            $tierPrice = $connection->getCheckSql(
                $tierExpr . ' IS NOT NULL',
                'ROUND((1 - ' . $tierExpr . ' / 100) * ' . $price . ', 4)',
                'NULL'
            );
            $finalPrice = $connection->getLeastSql([
                $price,
                $connection->getIfNullSql($specialPriceExpr, $price),
                $connection->getIfNullSql($tierPrice, $price),
            ]);
        } else {
            $finalPrice = new \Zend_Db_Expr('0');
            $tierPrice = $connection->getCheckSql($tierExpr . ' IS NOT NULL', '0', 'NULL');
        }

        $select->columns(
            [
                'price_type' => new \Zend_Db_Expr($priceType),
                'special_price' => $connection->getCheckSql($specialExpr, $specialPrice, '0'),
                'tier_percent' => $tierExpr,
                'orig_price' => $connection->getIfNullSql($price, '0'),
                'price' => $finalPrice,
                'min_price' => $finalPrice,
                'max_price' => $finalPrice,
                'tier_price' => $tierPrice,
                'base_tier' => $tierPrice,
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'catalog_product_prepare_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            ]
        );

        $query = $select->insertFromSelect($table);
        $connection->query($query);

        return $this;
    }

    /**
     * Calculate fixed bundle product selections price
     *
     * @return $this
     */
    protected function _calculateBundleOptionPrice()
    {
        $connection = $this->getConnection();

        $this->_prepareBundleSelectionTable();
        $this->_calculateBundleSelectionPrice(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $this->_calculateBundleSelectionPrice(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);

        $this->_prepareBundleOptionTable();

        $select = $connection->select()->from(
            $this->_getBundleSelectionTable(),
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        )->group(
            ['entity_id', 'customer_group_id', 'website_id', 'option_id']
        );
        $minPrice = $connection->getCheckSql('is_required = 1', 'price', 'NULL');
        $tierPrice = $connection->getCheckSql('is_required = 1', 'tier_price', 'NULL');
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('MIN(' . $minPrice . ')'),
                'alt_price' => new \Zend_Db_Expr('MIN(price)'),
                'max_price' => $connection->getCheckSql('group_type = 0', 'MAX(price)', 'SUM(price)'),
                'tier_price' => new \Zend_Db_Expr('MIN(' . $tierPrice . ')'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(tier_price)'),
            ]
        );

        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $connection->query($query);

        $this->_prepareDefaultFinalPriceTable();
        $this->applyBundlePrice();
        $this->applyBundleOptionPrice();

        return $this;
    }

    /**
     * Calculate bundle product selections price by product type
     *
     * @param int $priceType
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _calculateBundleSelectionPrice($priceType)
    {
        $connection = $this->getConnection();

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $selectionPriceValue = $connection->getCheckSql(
                'bsp.selection_price_value IS NULL',
                'bs.selection_price_value',
                'bsp.selection_price_value'
            );
            $selectionPriceType = $connection->getCheckSql(
                'bsp.selection_price_type IS NULL',
                'bs.selection_price_type',
                'bsp.selection_price_type'
            );
            $priceExpr = new \Zend_Db_Expr(
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.price * (' . $selectionPriceValue . ' / 100),4)',
                    $connection->getCheckSql(
                        'i.special_price > 0 AND i.special_price < 100',
                        'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100),4)',
                        $selectionPriceValue
                    )
                ) . '* bs.selection_qty'
            );

            $tierExpr = $connection->getCheckSql(
                'i.base_tier IS NOT NULL',
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)),4)',
                    $connection->getCheckSql(
                        'i.tier_percent > 0',
                        'ROUND((1 - i.tier_percent / 100) * ' . $selectionPriceValue . ',4)',
                        $selectionPriceValue
                    )
                ) . ' * bs.selection_qty',
                'NULL'
            );

            $priceExpr = $connection->getLeastSql([
                $priceExpr,
                $connection->getIfNullSql($tierExpr, $priceExpr),
            ]);
        } else {
            $price = 'idx.min_price * bs.selection_qty';
            $specialExpr = $connection->getCheckSql(
                'i.special_price > 0 AND i.special_price < 100',
                'ROUND(' . $price . ' * (i.special_price / 100), 4)',
                $price
            );
            $tierExpr = $connection->getCheckSql(
                'i.tier_percent IS NOT NULL',
                'ROUND((1 - i.tier_percent / 100) * ' . $price . ', 4)',
                'NULL'
            );
            $priceExpr = $connection->getLeastSql([
                $specialExpr,
                $connection->getIfNullSql($tierExpr, $price),
            ]);
        }

        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['i' => $this->_getBundlePriceTable()],
            ['entity_id', 'customer_group_id', 'website_id']
        )->join(
            ['parent_product' => $this->getTable('catalog_product_entity')],
            'parent_product.entity_id = i.entity_id',
            []
        )->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = parent_product.$linkField",
            ['option_id']
        )->join(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            ['selection_id']
        )->joinLeft(
            ['bsp' => $this->getTable('catalog_product_bundle_selection_price')],
            'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id',
            ['']
        )->join(
            ['idx' => $this->getIdxTable()],
            'bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id' .
            ' AND i.website_id = idx.website_id',
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'bs.product_id = e.entity_id AND e.required_options=0',
            []
        )->where(
            'i.price_type=?',
            $priceType
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr,
                'tier_price' => $tierExpr,
            ]
        );

        $query = $select->insertFromSelect($this->_getBundleSelectionTable());
        $connection->query($query);

        return $this;
    }

    /**
     * Prepare temporary index price for bundle products
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return $this
     */
    protected function _prepareBundlePrice($entityIds = null)
    {
        if (!$this->hasEntity() && empty($entityIds)) {
            return $this;
        }
        $this->_prepareTierPriceIndex($entityIds);
        $this->_prepareBundlePriceTable();
        $this->_prepareBundlePriceByType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED, $entityIds);
        $this->_prepareBundlePriceByType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC, $entityIds);

        $this->_calculateBundleOptionPrice();
        $this->_applyCustomOption();

        $this->_movePriceDataToIndexTable();

        return $this;
    }

    /**
     * Prepare percentage tier price for bundle products
     *
     * @param int|array $entityIds
     * @return $this
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $connection = $this->getConnection();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        // remove index by bundle products
        $select = $connection->select()->from(
            ['i' => $this->_getTierPriceIndexTable()],
            null
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "i.entity_id=e.entity_id",
            []
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        );
        $query = $select->deleteFromSelect('i');
        $connection->query($query);

        $select = $connection->select()->from(
            ['tp' => $this->getTable('catalog_product_entity_tier_price')],
            ['e.entity_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "tp.{$linkField} = e.{$linkField}",
            []
        )->join(
            ['cg' => $this->getTable('customer_group')],
            'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            'tp.website_id = 0 OR tp.website_id = cw.website_id',
            ['website_id']
        )->where(
            'cw.website_id != 0'
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        )->columns(
            new \Zend_Db_Expr('MIN(tp.value)')
        )->group(
            ['e.entity_id', 'cg.customer_group_id', 'cw.website_id']
        );

        if (!empty($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($this->_getTierPriceIndexTable());
        $connection->query($query);

        return $this;
    }

    private function applyBundlePrice()
    {
        $select = $this->getConnection()->select();
        $select->from(
            $this->_getBundlePriceTable(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'tax_class_id',
                'orig_price',
                'price',
                'min_price',
                'max_price',
                'tier_price',
                'base_tier',
            ]
        );

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $this->getConnection()->query($query);
    }

    private function applyBundleOptionPrice()
    {
        $connection = $this->getConnection();

        $subSelect = $connection->select()->from(
            $this->_getBundleOptionTable(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'min_price' => new \Zend_Db_Expr('SUM(min_price)'),
                'alt_price' => new \Zend_Db_Expr('MIN(alt_price)'),
                'max_price' => new \Zend_Db_Expr('SUM(max_price)'),
                'tier_price' => new \Zend_Db_Expr('SUM(tier_price)'),
                'alt_tier_price' => new \Zend_Db_Expr('MIN(alt_tier_price)'),
            ]
        )->group(
            ['entity_id', 'customer_group_id', 'website_id']
        );

        $minPrice = 'i.min_price + ' . $connection->getIfNullSql('io.min_price', '0');
        $tierPrice = 'i.tier_price + ' . $connection->getIfNullSql('io.tier_price', '0');
        $select = $connection->select()->join(
            ['io' => $subSelect],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        )->columns(
            [
                'min_price' => $connection->getCheckSql("{$minPrice} = 0", 'io.alt_price', $minPrice),
                'max_price' => new \Zend_Db_Expr('io.max_price + i.max_price'),
                'tier_price' => $connection->getCheckSql("{$tierPrice} = 0", 'io.alt_tier_price', $tierPrice),
            ]
        );

        $query = $select->crossUpdateFromSelect(['i' => $this->_getDefaultFinalPriceTable()]);
        $connection->query($query);
    }
}
