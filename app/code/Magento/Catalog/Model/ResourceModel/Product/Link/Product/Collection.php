<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Link\Product;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link as LinkModel;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Catalog product linked products collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Store product model
     *
     * @var Product
     */
    protected $_product;

    /**
     * Store product link model
     *
     * @var LinkModel
     */
    protected $_linkModel;

    /**
     * Store link type id
     *
     * @var int
     */
    protected $_linkTypeId;

    /**
     * Store strong mode flag that determine if needed for inner join or left join of linked products
     *
     * @var bool
     */
    protected $_isStrongMode;

    /**
     * Store flag that determine if product filter was enabled
     *
     * @var bool
     */
    protected $_hasLinkFilter = false;

    /**
     * @var string[]|null Root product link fields values.
     */
    private $productIds;

    /**
     * @var string|null
     */
    private $linkField;

    /**
     * Collection constructor.
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
     * @param string[]|null $productIds Root product IDs (linkFields, not entity_ids).
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
        ?array $productIds = null
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
            $categoryResourceModel
        );

        if ($productIds) {
            $this->productIds = $productIds;
            $this->_hasLinkFilter = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->_product = null;
        $this->_linkModel = null;
        $this->_linkTypeId = null;
        $this->_isStrongMode = null;
        $this->_hasLinkFilter = false;
        $this->productIds = null;
        $this->linkField = null;
    }

    /**
     * Declare link model and initialize type attributes join
     *
     * @param LinkModel $linkModel
     * @return $this
     */
    public function setLinkModel(LinkModel $linkModel)
    {
        $this->_linkModel = $linkModel;
        if ($linkModel->getLinkTypeId()) {
            $this->_linkTypeId = $linkModel->getLinkTypeId();
        }
        return $this;
    }

    /**
     * Enable strong mode for inner join of linked products
     *
     * @return $this
     */
    public function setIsStrongMode()
    {
        $this->_isStrongMode = true;
        return $this;
    }

    /**
     * Retrieve collection link model
     *
     * @return \Magento\Catalog\Model\Product\Link
     */
    public function getLinkModel()
    {
        return $this->_linkModel;
    }

    /**
     * Initialize collection parent product and add limitation join
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->_product = $product;
        if ($product && $product->getId()) {
            $this->_hasLinkFilter = true;
            $this->setStore($product->getStore());
            $this->productIds = [$product->getData($this->getLinkField())];
        }
        return $this;
    }

    /**
     * Retrieve collection base product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Exclude products from filter
     *
     * @param array $products
     * @return $this
     */
    public function addExcludeProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = [$products];
            }
            $this->_hasLinkFilter = true;
            $this->getSelect()->where(
                'links.linked_product_id NOT IN (?)',
                $products,
                \Zend_Db::INT_TYPE
            );
        }
        return $this;
    }

    /**
     * Add products to filter
     *
     * @param array|int|string $products
     * @return $this
     */
    public function addProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = [$products];
            }
            $identifierField = $this->getLinkField();
            $this->getSelect()->where(
                "product_entity_table.$identifierField IN (?)",
                $products,
                \Zend_Db::INT_TYPE
            );
            $this->_hasLinkFilter = true;
        }

        return $this;
    }

    /**
     * Add random sorting order
     *
     * @return $this
     */
    public function setRandomOrder()
    {
        $this->getSelect()->orderRand('main_table.entity_id');
        return $this;
    }

    /**
     * Setting group by to exclude duplications in collection
     *
     * @param string $groupBy
     * @return $this
     */
    public function setGroupBy($groupBy = 'e.entity_id')
    {
        $this->getSelect()->group($groupBy);
        return $this;
    }

    /**
     * Join linked products when specified link model
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->getLinkModel()) {
            $this->_joinLinks();
            $this->joinProductsToLinks();
        }
        return parent::_beforeLoad();
    }

    /**
     * Join linked products and their attributes
     *
     * @return $this
     */
    protected function _joinLinks()
    {
        $select = $this->getSelect();
        $connection = $select->getConnection();

        $joinCondition = [
            'links.linked_product_id = e.entity_id',
            $connection->quoteInto('links.link_type_id = ?', $this->_linkTypeId),
        ];
        $joinType = 'join';
        $linkField = $this->getLinkField();
        if ($this->productIds) {
            if ($this->_isStrongMode) {
                $this->getSelect()->where(
                    'links.product_id in (?)',
                    $this->productIds,
                    \Zend_Db::INT_TYPE
                );
            } else {
                $joinType = 'joinLeft';
                $joinCondition[] = $connection->quoteInto(
                    'links.product_id in (?)',
                    $this->productIds,
                    \Zend_Db::INT_TYPE
                );
            }
            if (count($this->productIds) === 1) {
                $this->addFieldToFilter(
                    $linkField,
                    ['neq' => array_values($this->productIds)[0]]
                );
            }
        } elseif ($this->_isStrongMode) {
            $this->addFieldToFilter(
                $linkField,
                ['eq' => -1]
            );
        }
        if ($this->_hasLinkFilter) {
            $select->{$joinType}(
                ['links' => $this->getTable('catalog_product_link')],
                implode(' AND ', $joinCondition),
                ['link_id' => 'link_id', '_linked_to_product_id' => 'product_id']
            );
            $this->joinAttributes();
        }
        return $this;
    }

    /**
     * Enable sorting products by its position
     *
     * @param string $dir sort type asc|desc
     * @return $this
     */
    public function setPositionOrder($dir = self::SORT_ORDER_ASC)
    {
        if ($this->_hasLinkFilter) {
            $this->getSelect()->order('position ' . $dir);
        }
        return $this;
    }

    /**
     * Enable sorting products by its attribute set name
     *
     * @param string $dir sort type asc|desc
     * @return $this
     */
    public function setAttributeSetIdOrder($dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->joinLeft(
            ['set' => $this->getTable('eav_attribute_set')],
            'e.attribute_set_id = set.attribute_set_id',
            ['attribute_set_name']
        )->order(
            'set.attribute_set_name ' . $dir
        );
        return $this;
    }

    /**
     * Join attributes
     *
     * @return $this
     */
    public function joinAttributes()
    {
        if (!$this->getLinkModel()) {
            return $this;
        }

        foreach ($this->getLinkAttributes() as $attribute) {
            $table = $this->getLinkModel()->getAttributeTypeTable($attribute['type']);
            $alias = sprintf('link_attribute_%s_%s', $attribute['code'], $attribute['type']);

            $joinCondiotion = [
                "{$alias}.link_id = links.link_id",
                $this->getSelect()->getConnection()
                    ->quoteInto("{$alias}.product_link_attribute_id = ?", $attribute['id']),
            ];
            $this->getSelect()->joinLeft(
                [$alias => $table],
                implode(' AND ', $joinCondiotion),
                [$attribute['code'] => 'value']
            );
        }

        return $this;
    }

    /**
     * Set sorting order
     *
     * $attribute can also be an array of attributes
     *
     * @param string|array $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if ($attribute == 'position') {
            return $this->setPositionOrder($dir);
        } elseif ($attribute == 'attribute_set_id') {
            return $this->setAttributeSetIdOrder($dir);
        }
        return parent::setOrder($attribute, $dir);
    }

    /**
     * Get attributes of specified link type
     *
     * @param int $type
     * @return array
     */
    public function getLinkAttributes($type = null)
    {
        return $this->getLinkModel()->getAttributes($type);
    }

    /**
     * Add link attribute to filter.
     *
     * @param string $code
     * @param array $condition
     * @return $this
     */
    public function addLinkAttributeToFilter($code, $condition)
    {
        foreach ($this->getLinkAttributes() as $attribute) {
            if ($attribute['code'] == $code) {
                $alias = sprintf('link_attribute_%s_%s', $code, $attribute['type']);
                $whereCondition = $this->_getConditionSql($alias . '.`value`', $condition);
                $this->getSelect()->where($whereCondition);
            }
        }
        return $this;
    }

    /**
     * Join Product To Links
     *
     * @return void
     */
    private function joinProductsToLinks()
    {
        if ($this->_hasLinkFilter) {
            $metaDataPool = $this->getProductEntityMetadata();
            $linkField = $this->getLinkField();
            $entityTable = $metaDataPool->getEntityTable();
            $this->getSelect()
                ->join(
                    ['product_entity_table' => $entityTable],
                    "links.product_id = product_entity_table.$linkField",
                    []
                );
        }
    }

    /**
     * Get product entity's identifier field.
     *
     * @return string
     */
    private function getLinkField(): string
    {
        if (!$this->linkField) {
            $this->linkField = $this->getProductEntityMetadata()->getLinkField();
        }

        return $this->linkField;
    }
}
