<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog rules resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Store number of seconds in a day
     */
    public const SECONDS_IN_DAY = 86400;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $_catalogRuleData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Catalog\Model\Product\ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * Rule constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Product\ConditionFactory $conditionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param PriceCurrencyInterface $priceCurrency
     * @param string|null $connectionName
     * @param EntityManager|null $entityManager
     * @param \Magento\Framework\DataObject|null $associatedEntityMap
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ConditionFactory $conditionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency,
        $connectionName = null,
        ?EntityManager $entityManager = null,
        ?\Magento\Framework\DataObject $associatedEntityMap = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_conditionFactory = $conditionFactory;
        $this->_coreDate = $coreDate;
        $this->_eavConfig = $eavConfig;
        $this->_eventManager = $eventManager;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_logger = $logger;
        $this->dateTime = $dateTime;
        $this->priceCurrency = $priceCurrency;
        $this->entityManager = $entityManager ?? ObjectManager::getInstance()->get(EntityManager::class);
        $this->_associatedEntitiesMap = $associatedEntityMap ?? ObjectManager::getInstance()
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
            ->get(\Magento\CatalogRule\Model\ResourceModel\Rule\AssociatedEntityMap::class)
            ->getData();

        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('catalogrule', 'rule_id');
    }

    /**
     * Get catalog rules product price for specific date, website and customer group
     *
     * @param \DateTimeInterface $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     * @return float|false
     */
    public function getRulePrice($date, $wId, $gId, $pId)
    {
        $data = $this->getRulePrices($date, $wId, $gId, [$pId]);
        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     *
     * Collect data with  product Id => price pairs
     *
     * @param \DateTimeInterface $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices(\DateTimeInterface $date, $websiteId, $customerGroupId, $productIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('catalogrule_product_price'), ['product_id', 'rule_price'])
            ->where('rule_date = ?', $date->format('Y-m-d'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds, \Zend_Db::INT_TYPE);

        return $connection->fetchPairs($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection->select()
            ->from($this->getTable('catalogrule_product'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date);

        return $connection->fetchAll($select);
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $this->entityManager->load($object, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * Delete the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }

    /**
     * Get product ids matching specified rules
     *
     * @param array $ruleIds
     * @return array
     */
    public function getProductIdsByRuleIds(array $ruleIds): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTable('catalogrule_product'),
                ['product_id']
            )
            ->where(
                'rule_id IN (?)',
                array_map('intval', $ruleIds)
            )
            ->distinct(
                true
            );
        return array_map('intval', $connection->fetchCol($select));
    }
}
