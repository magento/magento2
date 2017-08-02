<?php
/**
 * Configurable Products Price Indexer Resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Configurable extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
{
    /**
     * @var StoreResolverInterface
     * @since 2.2.0
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
     * @param string|null $connectionName
     * @param StoreResolverInterface|null $storeResolver
     * @since 2.2.0
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
        $this->storeResolver = $storeResolver ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            StoreResolverInterface::class
        );
    }

    /**
     * @param null|int|array $entityIds
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     * @since 2.0.0
     */
    protected function reindex($entityIds = null)
    {
        if ($this->hasEntity() || !empty($entityIds)) {
            $this->prepareFinalPriceDataForType($entityIds, $this->getTypeId());
            $this->_applyCustomOption();
            $this->_applyConfigurableOption();
            $this->_movePriceDataToIndexTable($entityIds);
        }
        return $this;
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getConfigurableOptionAggregateTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt_agr');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getConfigurableOptionPriceTable()
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_cfg_opt');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _applyConfigurableOption()
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $connection = $this->getConnection();
        $coaTable = $this->_getConfigurableOptionAggregateTable();
        $copTable = $this->_getConfigurableOptionPriceTable();
        $linkField = $metadata->getLinkField();

        $this->_prepareConfigurableOptionAggregateTable();
        $this->_prepareConfigurableOptionPriceTable();

        $subSelect = $this->getSelect();
        $subSelect->join(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.product_id = e.entity_id',
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.' . $linkField . ' = l.parent_id',
            ['parent_id' => 'entity_id']
        );

        $select = $connection->select();
        $select
            ->from(['sub' => new \Zend_Db_Expr('(' . (string)$subSelect . ')')], '')
            ->columns([
                'sub.parent_id',
                'sub.entity_id',
                'sub.customer_group_id',
                'sub.website_id',
                'sub.price',
                'sub.tier_price'
            ]);

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
