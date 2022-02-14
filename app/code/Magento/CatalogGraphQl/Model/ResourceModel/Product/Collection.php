<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\ResourceModel\Product;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogUrlRewrite\Model\Storage\DbStorage;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;
use Zend_Db_Select_Exception;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param AdapterInterface|null $connection
     * @param ProductLimitationFactory|null $productLimitationFactory
     * @param MetadataPool|null $metadataPool
     * @param TableMaintainer|null $tableMaintainer
     * @param PriceTableResolver|null $priceTableResolver
     * @param DimensionFactory|null $dimensionFactory
     * @param Category|null $categoryResourceModel
     * @param DbStorage|null $urlFinder
     * @param GalleryReadHandler|null $productGalleryReadHandler
     * @param Gallery|null $mediaGalleryResource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        AdapterInterface $connection = null,
        ProductLimitationFactory $productLimitationFactory = null,
        MetadataPool $metadataPool = null,
        TableMaintainer $tableMaintainer = null,
        PriceTableResolver $priceTableResolver = null,
        DimensionFactory $dimensionFactory = null,
        Category $categoryResourceModel = null,
        DbStorage $urlFinder = null,
        GalleryReadHandler $productGalleryReadHandler = null,
        Gallery $mediaGalleryResource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection,
            $productLimitationFactory,
            $metadataPool,
            $tableMaintainer,
            $priceTableResolver,
            $dimensionFactory,
            $categoryResourceModel,
            $urlFinder,
            $productGalleryReadHandler,
            $mediaGalleryResource
        );

        $this->tableMaintainer = $tableMaintainer ?: ObjectManager::getInstance()
            ->get(TableMaintainer::class);
    }

    /**
     * Join minimal position to the select
     *
     * @param array $categoryIds
     * @return void
     * @throws Zend_Db_Select_Exception
     */
    public function joinMinimalPosition(array $categoryIds): void
    {
        $this->_applyProductLimitations();
        $filters = $this->_productLimitationFilters;
        $positions = [];
        $connection = $this->getConnection();
        $select = $this->getSelect();

        foreach ($categoryIds as $categoryId) {
            $table = 'cat_index_' . $categoryId;
            $conditions = [
                $table . '.product_id=e.entity_id',
                $connection->quoteInto(
                    $table . '.store_id=?',
                    $filters['store_id'],
                    'int'
                ),
                $connection->quoteInto(
                    $table . '.category_id=?',
                    $categoryId,
                    'int'
                )
            ];

            $joinCond = implode(' AND ', $conditions);
            $fromPart = $select->getPart(Select::FROM);
            if (isset($fromPart[$table])) {
                $fromPart[$table]['joinCondition'] = $joinCond;
                $select->setPart(Select::FROM, $fromPart);
            } else {
                $select->joinLeft(
                    [$table => $this->tableMaintainer->getMainTable($this->getStoreId())],
                    $joinCond,
                    []
                );
            }
            $positions[] = $connection->getIfNullSql($table . '.position', '~0');
        }

        $columns = $select->getPart(Select::COLUMNS);
        $columnIndex = false;
        $minPos = $connection->getLeastSql($positions);
        foreach ($columns as $index => [,, $columnAlias]) {
            if ($columnAlias === 'cat_index_position') {
                $columnIndex = $index;
                break;
            }
        }
        if ($columnIndex) {
            $columns[$columnIndex][1] = $minPos;
            $select->setPart(Select::COLUMNS, $columns);
        } else {
            $select->columns(['cat_index_position' => $minPos]);
        }
        $this->_joinFields['position'] = ['table' => '', 'field' => 'cat_index_position'];
    }
}
