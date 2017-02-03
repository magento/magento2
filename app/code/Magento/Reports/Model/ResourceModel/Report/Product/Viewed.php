<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Most viewed product report aggregate resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Report\Product;

class Viewed extends \Magento\Sales\Model\ResourceModel\Report\AbstractReport
{
    /**
     * Aggregation key daily
     */
    const AGGREGATION_DAILY = 'report_viewed_product_aggregated_daily';

    /**
     * Aggregation key monthly
     */
    const AGGREGATION_MONTHLY = 'report_viewed_product_aggregated_monthly';

    /**
     * Aggregation key yearly
     */
    const AGGREGATION_YEARLY = 'report_viewed_product_aggregated_yearly';

    /**
     * Product resource instance
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * Resource helper instance
     *
     * @var \Magento\Reports\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Reports\Model\ResourceModel\Helper $resourceHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Reports\Model\ResourceModel\Helper $resourceHelper,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
        $this->_productResource = $productResource;
        $this->_resourceHelper = $resourceHelper;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate products view data
     *
     * @param null|mixed $from
     * @param null|mixed $to
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aggregate($from = null, $to = null)
    {
        $mainTable = $this->getMainTable();
        $connection = $this->getConnection();

        if ($from !== null || $to !== null) {
            $subSelect = $this->_getTableDateRangeSelect(
                $this->getTable('report_event'),
                'logged_at',
                'logged_at',
                $from,
                $to
            );
        } else {
            $subSelect = null;
        }
        $this->_clearTableByDateRange($mainTable, $from, $to, $subSelect);
        // convert dates to current admin timezone
        $periodExpr = $connection->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                ['source_table' => $this->getTable('report_event')],
                'source_table.logged_at',
                $from,
                $to
            )
        );
        $select = $connection->select();

        $select->group([$periodExpr, 'source_table.store_id', 'source_table.object_id']);

        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.event_id)');

        $columns = [
            'period' => $periodExpr,
            'store_id' => 'source_table.store_id',
            'product_id' => 'source_table.object_id',
            'product_name' => new \Zend_Db_Expr(
                sprintf('MIN(%s)', $connection->getIfNullSql('product_name.value', 'product_default_name.value'))
            ),
            'product_price' => new \Zend_Db_Expr(
                sprintf(
                    'MIN(%s)',
                    $connection->getIfNullSql(
                        $connection->getIfNullSql('product_price.value', 'product_default_price.value'),
                        0
                    )
                )
            ),
            'views_num' => $viewsNumExpr,
        ];

        $select->from(
            ['source_table' => $this->getTable('report_event')],
            $columns
        )->where(
            'source_table.event_type_id = ?',
            \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW
        );

        $select->joinInner(
            ['product' => $this->getTable('catalog_product_entity')],
            'product.entity_id = source_table.object_id',
            []
        );

        // join product attributes Name & Price
        $nameAttribute = $this->_productResource->getAttribute('name');
        $joinExprProductName = [
            'product_name.entity_id = product.entity_id',
            'product_name.store_id = source_table.store_id',
            $connection->quoteInto('product_name.attribute_id = ?', $nameAttribute->getAttributeId()),
        ];
        $joinExprProductName = implode(' AND ', $joinExprProductName);
        $joinProductName = [
            'product_default_name.entity_id = product.entity_id',
            'product_default_name.store_id = 0',
            $connection->quoteInto('product_default_name.attribute_id = ?', $nameAttribute->getAttributeId()),
        ];
        $joinProductName = implode(' AND ', $joinProductName);
        $select->joinLeft(
            ['product_name' => $nameAttribute->getBackend()->getTable()],
            $joinExprProductName,
            []
        )->joinLeft(
            ['product_default_name' => $nameAttribute->getBackend()->getTable()],
            $joinProductName,
            []
        );
        $priceAttribute = $this->_productResource->getAttribute('price');
        $joinExprProductPrice = [
            'product_price.entity_id = product.entity_id',
            'product_price.store_id = source_table.store_id',
            $connection->quoteInto('product_price.attribute_id = ?', $priceAttribute->getAttributeId()),
        ];
        $joinExprProductPrice = implode(' AND ', $joinExprProductPrice);

        $joinProductPrice = [
            'product_default_price.entity_id = product.entity_id',
            'product_default_price.store_id = 0',
            $connection->quoteInto('product_default_price.attribute_id = ?', $priceAttribute->getAttributeId()),
        ];
        $joinProductPrice = implode(' AND ', $joinProductPrice);
        $select->joinLeft(
            ['product_price' => $priceAttribute->getBackend()->getTable()],
            $joinExprProductPrice,
            []
        )->joinLeft(
            ['product_default_price' => $priceAttribute->getBackend()->getTable()],
            $joinProductPrice,
            []
        );

        $havingPart = [$connection->prepareSqlCondition($viewsNumExpr, ['gt' => 0])];
        if (null !== $subSelect) {
            $subSelectHavingPart = $this->_makeConditionFromDateRangeSelect($subSelect, 'period');
            if ($subSelectHavingPart) {
                $havingPart[] = '(' . $subSelectHavingPart . ')';
            }
        }
        $select->having(implode(' AND ', $havingPart));

        $select->useStraightJoin();
        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);

        $this->_resourceHelper->updateReportRatingPos(
            $connection,
            'day',
            'views_num',
            $mainTable,
            $this->getTable(self::AGGREGATION_DAILY)
        );
        $this->_resourceHelper->updateReportRatingPos(
            $connection,
            'month',
            'views_num',
            $mainTable,
            $this->getTable(self::AGGREGATION_MONTHLY)
        );
        $this->_resourceHelper->updateReportRatingPos(
            $connection,
            'year',
            'views_num',
            $mainTable,
            $this->getTable(self::AGGREGATION_YEARLY)
        );
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE);
        return $this;
    }
}
