<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Report;

/**
 * Bestsellers report resource model
 */
class Bestsellers extends AbstractReport
{
    const AGGREGATION_DAILY = 'daily';

    const AGGREGATION_MONTHLY = 'monthly';

    const AGGREGATION_YEARLY = 'yearly';

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Sales\Model\Resource\Helper
     */
    protected $_salesResourceHelper;

    /**
     * Ignored product types list
     *
     * @var array
     */
    protected $ignoredProductTypes = [
        \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
    ];

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Catalog\Model\Resource\Product $productResource
     * @param \Magento\Sales\Model\Resource\Helper $salesResourceHelper
     * @param array $ignoredProductTypes
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Catalog\Model\Resource\Product $productResource,
        \Magento\Sales\Model\Resource\Helper $salesResourceHelper,
        array $ignoredProductTypes = []
    ) {
        parent::__construct($resource, $logger, $localeDate, $reportsFlagFactory, $dateTime, $timezoneValidator);
        $this->_productResource = $productResource;
        $this->_salesResourceHelper = $salesResourceHelper;
        $this->ignoredProductTypes = array_merge($this->ignoredProductTypes, $ignoredProductTypes);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_bestsellers_aggregated_' . self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\Zend_Date|array|null $from
     * @param string|int|\Zend_Date|array|null $to
     * @return $this
     * @throws \Exception
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $adapter = $this->_getWriteAdapter();
        //$this->_getWriteAdapter()->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales_order'),
                    'created_at',
                    'updated_at',
                    $from,
                    $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    ['source_table' => $this->getTable('sales_order')],
                    'source_table.created_at',
                    $from,
                    $to
                )
            );
            $select = $adapter->select();

            $select->group([$periodExpr, 'source_table.store_id', 'order_item.product_id']);

            $columns = [
                'period' => $periodExpr,
                'store_id' => 'source_table.store_id',
                'product_id' => 'order_item.product_id',
                'product_name' => new \Zend_Db_Expr(
                    sprintf('MIN(%s)', $adapter->getIfNullSql('product_name.value', 'product_default_name.value'))
                ),
                'product_price' => new \Zend_Db_Expr(
                    sprintf(
                        '%s * %s',
                        new \Zend_Db_Expr(
                            sprintf(
                                'MIN(%s)',
                                $adapter->getIfNullSql(
                                    $adapter->getIfNullSql('product_price.value', 'product_default_price.value'),
                                    0
                                )
                            )
                        ),
                        new \Zend_Db_Expr(
                            sprintf('MIN(%s)', $adapter->getIfNullSql('source_table.base_to_global_rate', '0'))
                        )
                    )
                ),
                'qty_ordered' => new \Zend_Db_Expr('SUM(order_item.qty_ordered)'),
            ];

            $select->from(
                ['source_table' => $this->getTable('sales_order')],
                $columns
            )->joinInner(
                ['order_item' => $this->getTable('sales_order_item')],
                'order_item.order_id = source_table.entity_id',
                []
            )->where(
                'source_table.state != ?',
                \Magento\Sales\Model\Order::STATE_CANCELED
            );

            $joinExpr = [
                'product.entity_id = order_item.product_id',
                $adapter->quoteInto('product.entity_type_id = ?', $this->_productResource->getTypeId()),
                $adapter->quoteInto('product.type_id NOT IN(?)', $this->ignoredProductTypes),
            ];

            $joinExpr = implode(' AND ', $joinExpr);
            $select->joinInner(['product' => $this->getTable('catalog_product_entity')], $joinExpr, []);

            // join product attributes Name & Price
            $attr = $this->_productResource->getAttribute('name');
            $joinExprProductName = [
                'product_name.entity_id = product.entity_id',
                'product_name.store_id = source_table.store_id',
                $adapter->quoteInto('product_name.entity_type_id = ?', $this->_productResource->getTypeId()),
                $adapter->quoteInto('product_name.attribute_id = ?', $attr->getAttributeId()),
            ];
            $joinExprProductName = implode(' AND ', $joinExprProductName);
            $joinProductName = [
                'product_default_name.entity_id = product.entity_id',
                'product_default_name.store_id = 0',
                $adapter->quoteInto('product_default_name.entity_type_id = ?', $this->_productResource->getTypeId()),
                $adapter->quoteInto('product_default_name.attribute_id = ?', $attr->getAttributeId()),
            ];
            $joinProductName = implode(' AND ', $joinProductName);
            $select->joinLeft(
                ['product_name' => $attr->getBackend()->getTable()],
                $joinExprProductName,
                []
            )->joinLeft(
                ['product_default_name' => $attr->getBackend()->getTable()],
                $joinProductName,
                []
            );
            $attr = $this->_productResource->getAttribute('price');
            $joinExprProductPrice = [
                'product_price.entity_id = product.entity_id',
                'product_price.store_id = source_table.store_id',
                $adapter->quoteInto('product_price.entity_type_id = ?', $this->_productResource->getTypeId()),
                $adapter->quoteInto('product_price.attribute_id = ?', $attr->getAttributeId()),
            ];
            $joinExprProductPrice = implode(' AND ', $joinExprProductPrice);

            $joinProductPrice = [
                'product_default_price.entity_id = product.entity_id',
                'product_default_price.store_id = 0',
                $adapter->quoteInto('product_default_price.entity_type_id = ?', $this->_productResource->getTypeId()),
                $adapter->quoteInto('product_default_price.attribute_id = ?', $attr->getAttributeId()),
            ];
            $joinProductPrice = implode(' AND ', $joinProductPrice);
            $select->joinLeft(
                ['product_price' => $attr->getBackend()->getTable()],
                $joinExprProductPrice,
                []
            )->joinLeft(
                ['product_default_price' => $attr->getBackend()->getTable()],
                $joinProductPrice,
                []
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->useStraightJoin();
            // important!
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $adapter->query($insertQuery);

            $columns = [
                'period' => 'period',
                'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                'product_id' => 'product_id',
                'product_name' => new \Zend_Db_Expr('MIN(product_name)'),
                'product_price' => new \Zend_Db_Expr('MIN(product_price)'),
                'qty_ordered' => new \Zend_Db_Expr('SUM(qty_ordered)'),
            ];

            $select->reset();
            $select->from(
                $this->getMainTable(),
                $columns
            )->where(
                'store_id <> ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(['period', 'product_id']);
            $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $adapter->query($insertQuery);

            // update rating
            $this->_updateRatingPos(self::AGGREGATION_DAILY);
            $this->_updateRatingPos(self::AGGREGATION_MONTHLY);
            $this->_updateRatingPos(self::AGGREGATION_YEARLY);
            $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_BESTSELLERS_FLAG_CODE);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Update rating position
     *
     * @param string $aggregation One of \Magento\Sales\Model\Resource\Report\Bestsellers::AGGREGATION_XXX constants
     * @return $this
     */
    protected function _updateRatingPos($aggregation)
    {
        $aggregationTable = $this->getTable('sales_bestsellers_aggregated_' . $aggregation);

        $aggregationAliases = [
            'daily' => self::AGGREGATION_DAILY,
            'monthly' => self::AGGREGATION_MONTHLY,
            'yearly' => self::AGGREGATION_YEARLY,
        ];
        $this->_salesResourceHelper->getBestsellersReportUpdateRatingPos(
            $aggregation,
            $aggregationAliases,
            $this->getMainTable(),
            $aggregationTable
        );
        return $this;
    }
}
