<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Address;

use Magento\Sales\Api\Data\OrderAddressSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Order addresses collection
 */
class Collection extends AbstractCollection implements OrderAddressSearchResultInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_address_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_address_collection';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Snapshot $entitySnapshot
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        AdapterInterface $connection = null,
        AbstractDb $resource = null,
        ResolverInterface $localeResolver = null
    ) {
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()
            ->get(ResolverInterface::class);
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Address::class,
            \Magento\Sales\Model\ResourceModel\Order\Address::class
        );
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinRegionNameTable();
 	 	return $this;
    }

    /**
     * Redeclare after load method for dispatch event
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);

        return $this;
    }

    /**
     * Join region name table by current locale
     *
     * @return $this
     */
    private function joinRegionNameTable()
    {
        $locale = $this->localeResolver->getLocale();

        $connection = $this->getConnection();
        $regionIdField = $connection->quoteIdentifier('main_table.region_id');
        $localeCondition = $connection->quoteInto("rnt.locale=?", $locale);
        $this->getSelect()
 	 	    ->joinLeft(
 	 	 	    ['rct' => $this->getTable('directory_country_region')],
 	 	 	"rct.region_id={$regionIdField}",
                []
            )->joinLeft(
                ['rnt' => $this->getTable('directory_country_region_name')],
            "rnt.region_id={$regionIdField} AND {$localeCondition}",
                ['region' => $this->getRegionNameExpresion()]
            );

        return $this;
    }

    /**
     * Get SQL Expresion to define Region Name field by locale
     *
     * @return \Zend_Db_Expr
     */
    private function getRegionNameExpresion(): \Zend_Db_Expr
    {
        $connection = $this->getConnection();
        $defaultNameExpr = $connection->getIfNullSql(
            $connection->quoteIdentifier('rct.default_name'),
            $connection->quoteIdentifier('main_table.region')
        );

        return $connection->getIfNullSql(
            $connection->quoteIdentifier('rnt.name'),
            $defaultNameExpr
        );
    }
}
