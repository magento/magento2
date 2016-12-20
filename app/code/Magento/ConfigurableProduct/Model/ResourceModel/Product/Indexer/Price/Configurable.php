<?php
/**
 * Configurable Products Price Indexer Resource model
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;

class Configurable extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
{
    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        $connectionName = null,
        StoreResolverInterface $storeResolver = null
    ) {
        parent::__construct($context, $tableStrategy, $eavConfig, $eventManager, $moduleManager, $connectionName);
        $this->storeResolver = $storeResolver ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(StoreResolverInterface::class);
    }

    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->beginTransaction();
        try {
            $this->reindex();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    public function reindexEntity($entityIds)
    {
        $this->reindex($entityIds);
        return $this;
    }

    /**
     * @param null|int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function reindex($entityIds = null)
    {
        if ($this->hasEntity() || !empty($entityIds)) {
            if (!empty($entityIds)) {
                $allEntityIds = $this->getRelatedProducts($entityIds);
                $this->prepareFinalPriceDataForType($allEntityIds, null);
            } else {
                $this->_prepareFinalPriceData($entityIds);
            }
            $this->_applyCustomOption();
            $this->_applyConfigurableOption($entityIds);
            $this->_movePriceDataToIndexTable($entityIds);
        }
        return $this;
    }

    /**
     * Get related product
     *
     * @param int[] $entityIds
     * @return int[]
     */
    private function getRelatedProducts($entityIds)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $select = $this->getConnection()->select()->union(
            [
                $this->getConnection()->select()
                    ->from(
                        ['e' => $this->getTable('catalog_product_entity')],
                        'e.entity_id'
                    )->join(
                        ['cpsl' => $this->getTable('catalog_product_super_link')],
                        'cpsl.parent_id = e.' . $metadata->getLinkField(),
                        []
                    )->where(
                        'e.entity_id IN (?)',
                        $entityIds
                    ),
                $this->getConnection()->select()
                    ->from(
                        ['cpsl' => $this->getTable('catalog_product_super_link')],
                        'cpsl.product_id'
                    )->join(
                        ['e' => $this->getTable('catalog_product_entity')],
                        'cpsl.parent_id = e.' . $metadata->getLinkField(),
                        []
                    )->where(
                        'e.entity_id IN (?)',
                        $entityIds
                    ),
                $this->getConnection()->select()
                    ->from($this->getTable('catalog_product_super_link'), 'product_id')
                    ->where('product_id in (?)', $entityIds),
            ]
        );

        return array_map('intval', $this->getConnection()->fetchCol($select));
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    protected function _getConfigurableOptionAggregateTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt_agr');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    protected function _getConfigurableOptionPriceTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function _prepareConfigurableOptionAggregateTable()
    {
        $this->getConnection()->delete($this->_getConfigurableOptionAggregateTable());
        return $this;
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     */
    protected function _prepareConfigurableOptionPriceTable()
    {
        $this->getConnection()->delete($this->_getConfigurableOptionPriceTable());
        return $this;
    }

    /**
     * Calculate minimal and maximal prices for configurable product options
     * and apply it to final price
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _applyConfigurableOption()
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $connection = $this->getConnection();
        $coaTable = $this->_getConfigurableOptionAggregateTable();
        $copTable = $this->_getConfigurableOptionPriceTable();

        $this->_prepareConfigurableOptionAggregateTable();
        $this->_prepareConfigurableOptionPriceTable();

        $statusAttribute = $this->_getAttribute(ProductInterface::STATUS);
        $linkField = $metadata->getLinkField();

        $select = $connection->select()->from(
            ['i' => $this->_getDefaultFinalPriceTable()],
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = i.entity_id',
            ['parent_id' => 'e.entity_id']
        )->join(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.parent_id = e.' . $linkField,
            ['product_id']
        )->columns(
            ['customer_group_id', 'website_id'],
            'i'
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.product_id',
            []
        )->where(
            'le.required_options=0'
        )->joinLeft(
            ['status_global_attr' => $statusAttribute->getBackendTable()],
            "status_global_attr.{$linkField} = le.{$linkField}"
            . ' AND status_global_attr.attribute_id  = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_global_attr.store_id  = ' . Store::DEFAULT_STORE_ID,
            []
        )->joinLeft(
            ['status_attr' => $statusAttribute->getBackendTable()],
            "status_attr.{$linkField} = le.{$linkField}"
            . ' AND status_attr.attribute_id  = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_attr.store_id  = ' . $this->storeResolver->getCurrentStoreId(),
            []
        )->where(
            'IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED
        )->group(
            ['e.entity_id', 'i.customer_group_id', 'i.website_id', 'l.product_id']
        );
        $priceColumn = $this->_addAttributeToSelect($select, 'price', 'le.' . $linkField, 0, null, true);
        $tierPriceColumn = $connection->getIfNullSql('MIN(i.tier_price)', 'NULL');

        $select->columns(
            ['price' => $priceColumn, 'tier_price' => $tierPriceColumn]
        );

        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $connection->select()->from(
            [$coaTable],
            [
                'parent_id',
                'customer_group_id',
                'website_id',
                'MIN(price)',
                'MAX(price)',
                'MIN(tier_price)',
            ]
        )->group(
            ['parent_id', 'customer_group_id', 'website_id']
        );

        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        $table = ['i' => $this->_getDefaultFinalPriceTable()];
        $select = $connection->select()->join(
            ['io' => $copTable],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        );
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price - i.orig_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price - i.orig_price + io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $select->crossUpdateFromSelect($table);
        $connection->query($query);

        $connection->delete($coaTable);
        $connection->delete($copTable);

        return $this;
    }
}
